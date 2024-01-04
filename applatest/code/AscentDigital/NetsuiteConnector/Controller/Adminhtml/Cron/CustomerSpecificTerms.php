<?php
//CHM MA
namespace AscentDigital\NetsuiteConnector\Controller\Adminhtml\Cron;

use Magento\Framework\Filesystem\DirectoryList as Directory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\ResourceConnection;
use AscentDigital\NetsuiteConnector\Model\NSCronFactory;


class CustomerSpecificTerms extends \Magento\Backend\App\Action
{


    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $directory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;
    protected $nsCron;



    /**
     * @param \Magento\Framework\App\Action\Context $context,
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Directory $directory,
        CustomerFactory $customerFactory,
        ResourceConnection $resourceConnection,
        NSCronFactory $nsCron

    ) {
        parent::__construct($context);
        $this->directory = $directory;
        $this->customerFactory = $customerFactory;
        $this->resourceConnection = $resourceConnection;
        $this->nsCron = $nsCron;
    }

    /**
     * Test Order Create Shipment Controller
     */
    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/netsuite_manual_cron.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info("Customer Specific Terms cron is executed.");
        $root = $this->directory->getRoot();
        require_once($root . '/lib/PHPToolkit/NetSuiteService.php');
        $service = new \NetSuiteService();
        $service->setSearchPreferences(false, 50);
        $cron = $this->nsCron->create()->load('customer_terms_cron', 'title');
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
                    $cron->setTitle('customer_terms_cron');
                    $cron->setSearchId($searchResult->searchId);
                    $cron->save();
                } else {
                    $cron->setIndex(0);
                    $cron->setTotalPages('');
                    $cron->setTitle('customer_terms_cron');
                    $cron->setSearchId('');
                    $cron->save();
                }
            } else {
                $searchResponse = $this->searchTermsInNetsuite($service, $cron);
            }
        } else {
            $searchResponse = $this->searchTermsInNetsuite($service, $cron);
        }
        $this->processSearchResponse($searchResponse, $logger, $root);
        $logger->info("Customer Specific Terms cron is finished.");
        // get customer internal ids from magento 


    }

    public function searchTermsInNetsuite($service, $cron)
    {
        $customerInternalIds = $this->getCustomerInternalIds();
        $internalIds = array();
        // foreach ($customerInternalIds as $customerInternalId) {
            $internalId = new \RecordRef();
            // $internalId->internalId = $customerInternalId['value'];;
            $internalId->internalId = '208';
            $internalIds[] = $internalId;
        // }
        // customer search condition
        $internalIdSearchField = new \SearchMultiSelectField();
        $internalIdSearchField->operator = "anyOf";
        $internalIdSearchField->searchValue = $internalIds;
        // first name search for contact
        $name = new \SearchStringField();
        $name->operator = 'contains';
        $name->searchValue = 'Brooke';
        // customer search 
        $customerSearch = new \EmployeeSearchBasic();
        // $customerSearch->internalId = $internalIdSearchField;
        $customerSearch->firstName = $name;

        // creating request
        $request = new \SearchRequest();
        $request->searchRecord = $customerSearch;

        //make soap call of the created request
        $searchResponse = $service->search($request);
        $searchResult = $searchResponse->searchResult;
        $totalPages = $searchResult->totalPages;
        $pageIndex = $searchResponse->searchResult->pageIndex;
        if ($totalPages > 1) {
            if ($cron->getData()) {
                $cron->setIndex($pageIndex + 1);
                $cron->setTotalPages($totalPages);
                $cron->setTitle('customer_terms_cron');
                $cron->setSearchId($searchResult->searchId);
                $cron->save();
            } else {
                $cron = $this->nsCron->create();
                $cron->setIndex($pageIndex + 1);
                $cron->setTotalPages($totalPages);
                $cron->setTitle('customer_terms_cron');
                $cron->setSearchId($searchResult->searchId);
                $cron->save();
            }
        }
        return $searchResponse;
    }

    public function processSearchResponse($searchResponse, $logger, $root)
    {
        if (!$searchResponse->searchResult->status->isSuccess) {
            $messages = $searchResponse->searchResult->status->statusDetail;
            foreach ($messages as $message) {
                $logger->debug('AscentDigital\NetsuiteConnector\Controller\Adminhtml\Cron\CustomerSpecificTerms ' . $message->message);
            }
        } else {
            $customers = $searchResponse->searchResult->recordList->record;
            foreach ($customers as $customer) {
                $terms = [
                    'Net 15' => '1',
                    'Net 20' => '2',
                    'Net 30' => '3',
                    '1% 10 Net 30' => '4',
                    '2% 10 Net 30' => '5',
                    'Net 45' => '6',
                    '2% 30 Net 60' => '7',
                    '2% Net 30' => '8',
                    '3% 10 Net 60' => '9',
                    'Net 10' => '10',
                    'Net 60' => '11',
                    'Net Due 15th Following Month' => '12'
                ];

                $carriers = [
                    '_ups' => '1',
                    '_usps' => '2',
                    '_fedex' => '3',
                ];
                $customFieldList = $customer->customFieldList;
                $custentity_allowed_adv_exc_options = array();
                $msgAccountManager = array();
                if ($customFieldList) {
                    $customFields = $customFieldList->customField;
                    if ($customFields) {
                        foreach ($customFields as $customField) {
                            if ($customField->scriptId == 'custentity_allowed_adv_exc_options') {
                                $options = $customField->value;
                                if ($options) {
                                    foreach ($options as $option) {
                                        if ($option->name) {
                                            $custentity_allowed_adv_exc_options[] = $option->name;
                                        }
                                    }
                                }
                            } elseif ($customField->scriptId == 'custentity8') {
                                $msgAccountManager = $customField->value;
                            }
                        }
                    }
                }
                $ns_terms = $customer->terms;
                $carrier = $customer->thirdPartyCarrier;

                if ($ns_terms || $custentity_allowed_adv_exc_options || $msgAccountManager || $carrier) {
                    $customers = $this->customerFactory->create()->getCollection()
                        ->addAttributeToSelect("*")
                        ->addAttributeToFilter("ns_internal_id", $customer->internalId)
                        ->load();
                    foreach ($customers as $customer) {

                        // save customer terms
                        if ($ns_terms && $ns_terms->name) {
                            $customer->setUseYourTermsTitle($terms[$ns_terms->name]);
                            $customer->setUseMyTerms(true);
                            $customer->save();
                        }

                        // save allowed advance exchange options or depot service option
                        if ($custentity_allowed_adv_exc_options) {
                            // implode options by ","
                            $allowedOptions = implode(",", $custentity_allowed_adv_exc_options);
                            $customer->setAlwdDepotServiceType($allowedOptions);
                            $customer->save();
                        }

                        // save MSG Account Manager
                        if ($msgAccountManager && $msgAccountManager->name) {
                            $customer->setMsgAccountManager($msgAccountManager->name);
                            $customer->save();
                        }

                        // save carrier
                        if ($carrier) {
                            $customer->setCustomerCarrier($carriers[$carrier]);
                            $customer->save();
                        }
                    }
                }
            }
            // echo 'term set successfully!';
        }
        $logger->info("Customer Specific Terms cron is finished.");
    }

    /**
     * getCustomerInternalIds
     * get customer internal ids from magento
     * return 
     */
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
}
