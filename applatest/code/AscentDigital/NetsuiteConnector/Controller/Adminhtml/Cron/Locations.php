<?php

namespace AscentDigital\NetsuiteConnector\Controller\Adminhtml\Cron;

use Magento\Framework\Filesystem\DirectoryList as Directory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\ResourceConnection;
use AscentDigital\NetsuiteConnector\Model\NSCronFactory;
class Locations extends \Magento\Backend\App\Action
{
    
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
     /**
     * @var \AscentDigital\NetsuiteConnector\Model\NSCronFactory
     */
    protected $nsCron;

       /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $directory;
    /**
    * Constructor
    *
    */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        CustomerFactory $customerFactory,
        Directory $directory,
        ResourceConnection $resourceConnection,
        NSCronFactory $nsCron

    ) {
        $this->customerFactory = $customerFactory;
        $this->resourceConnection = $resourceConnection;
        $this->nsCron = $nsCron;
        $this->directory = $directory;
        parent::__construct($context);
    }

    public function execute()
    {  
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/netsuite_manual_cron.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info("Locations cron is executed."); 
        //add your cron job logic here.
        $root = $this->directory->getRoot();
        require_once($root . '/lib/PHPToolkit/NetSuiteService.php');
        $service = new \NetSuiteService();

        $service->setSearchPreferences(false, 50);
        $cron = $this->nsCron->create()->load('location_cron', 'title');
        if ($cron->getData()) {
            if ($cron->getIndex() > 1) {
                $SearchMoreWithIdRequest = new \SearchMoreWithIdRequest();
                // assigning search id 
                $SearchMoreWithIdRequest->searchId = $cron->getSearchId();

                //assigning next page index
                $SearchMoreWithIdRequest->pageIndex = $cron->getIndex();

                // search next page result on the basis of search id
                $searchResponse = $service->searchMoreWithId($SearchMoreWithIdRequest);
                $searchResult = $searchResponse->searchResult;
                $totalPages = $searchResult->totalPages;
                $pageIndex = $searchResponse->searchResult->pageIndex;
                if ($pageIndex < $totalPages) {
                    $cron->setIndex($pageIndex + 1);
                    $cron->setTotalPages($totalPages);
                    $cron->setTitle('location_cron');
                    $cron->setSearchId($searchResult->searchId);
                    $cron->save();
                } else {
                    $cron->setIndex(0);
                    $cron->setTotalPages('');
                    $cron->setTitle('location_cron');
                    $cron->setSearchId('');
                    $cron->save();
                }
            } else {
                $searchResponse = $this->searchLocationsInNetsuite($service, $cron);
            }
        } else {
            $searchResponse = $this->searchLocationsInNetsuite($service, $cron);
        }
        echo "<pre>";print_r($searchResponse);die("dieee");

        $this->processSearchResponse($searchResponse, $logger);
        $logger->info("Locations cron is finished.");
    }

    public function searchLocationsInNetsuite($service, $cron)
    {
        // customer internal ids
        $internalIds = $this->getCustomerInternalIds();
        $customerinternalIds = array();
        foreach ($internalIds as $internalId) {
            $customerInternalId = new \ListOrRecordRef();
            $customerInternalId->internalId = $internalId['value'];
            // $customerInternalId->internalId = '42346';
            $customerinternalIds[] = $customerInternalId;
        }
        // search location on custom field (customer internal id)
        // locations search condition
        $internalIdSearchField = new \SearchMultiSelectCustomField();
        $internalIdSearchField->operator = "anyOf";
        $internalIdSearchField->scriptId = "custrecord_location_customer";
        $internalIdSearchField->searchValue = $customerinternalIds;

        $customFieldList = new \SearchCustomFieldList();
        $customFieldList->customField = [$internalIdSearchField];


        $locationSearch = new \LocationSearchBasic();
        $locationSearch->customFieldList = $customFieldList;


        $request = new \SearchRequest();
        $request->searchRecord = $locationSearch;

        $searchResponse = $service->search($request);
        $searchResult = $searchResponse->searchResult;
        $totalPages = $searchResult->totalPages;
        $pageIndex = $searchResponse->searchResult->pageIndex;
        if ($totalPages > 1) {
            if ($cron->getData()) {
                $cron->setIndex($pageIndex + 1);
                $cron->setTotalPages($totalPages);
                $cron->setTitle('location_cron');
                $cron->setSearchId($searchResult->searchId);
                $cron->save();
            } else {
                $cron = $this->nsCron->create();
                $cron->setIndex($pageIndex + 1);
                $cron->setTotalPages($totalPages);
                $cron->setTitle('location_cron');
                $cron->setSearchId($searchResult->searchId);
                $cron->save();
            }
        }
        return $searchResponse;
    }

    public function processSearchResponse($searchResponse, $logger)
    {
        if (!$searchResponse->searchResult->status->isSuccess) {
            echo "SEARCH ERROR";
        } else {
            $records = $searchResponse->searchResult->recordList->record;
            $this->setCustomerLocationId($records);
        }
    }

    public function getCustomerInternalIds()
    {
        $connection = $this->resourceConnection->getConnection();
        // $table is table name
        $table = $connection->getTableName('customer_entity_varchar');
        //For Select query
        $query = "Select value FROM " . $table . " WHERE attribute_id = 175 GROUP BY value";
        $results = $connection->fetchAll($query);
        return $results;
    }
    public function setCustomerLocationId($records)
    {
        $customerIds = array();
        foreach ($records as $record) {
            $customFieldList = $record->customFieldList;
            if ($customFieldList) {
                $customFields = $customFieldList->customField;
                foreach ($customFields as $customField) {
                    $scriptId = $customField->scriptId;
                    if ($scriptId == 'custrecord_location_customer') {
                        $internalId = $customField->value->internalId;
                        if (array_key_exists($internalId, $customerIds)) {
                            $customerIds[$internalId][] = $record->internalId;
                        } else {
                            $customerIds[$internalId] = [$record->internalId];
                        }
                        $internalId = $customField->value->internalId;
                        //   echo "<br>location id: " . $record->internalId . "   Customer internal id: " . $internalId . "<br>";
                    }
                }
            }
        }
        $customerList = array();
        foreach ($customerIds as $key => $customerId) {
            $customers = $this->customerFactory->create()->getCollection()
                ->addAttributeToSelect("*")
                ->addAttributeToFilter("ns_internal_id", $key)
                ->load();
            foreach ($customers as $customer) {
                $customer->setLocationId(implode(',', $customerId));
                $customer->save();
            }
        }
    }
}