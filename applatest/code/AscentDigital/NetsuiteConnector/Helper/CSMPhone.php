<?php

namespace AscentDigital\NetsuiteConnector\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\ResourceConnection;
use AscentDigital\NetsuiteConnector\Model\NSCronFactory;
use Magento\Framework\Filesystem\DirectoryList as Directory;

class CSMPhone extends AbstractHelper
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
    protected $logger;


    public function __construct(
        Directory $directory,
        CustomerFactory $customerFactory,
        ResourceConnection $resourceConnection,
        NSCronFactory $nsCron,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->directory = $directory;
        $this->customerFactory = $customerFactory;
        $this->resourceConnection = $resourceConnection;
        $this->nsCron = $nsCron;
        $this->logger = $logger;
    }

    public function getCSMPhone()
    {
        try {
            $root = $this->directory->getRoot();

            require_once($root . '/lib/PHPToolkit/NetSuiteService.php');
            $service = new \NetSuiteService();
            $service->setPreferences(false, true);
            $managers = $this->getMcgAccountManager();
            foreach ($managers as $manager) {
                $entityId = $manager;
                // entity id search for contact
                $name = new \SearchStringField();
                $name->operator = 'contains';
                $name->searchValue = $entityId;
                // customer search 
                $customerSearch = new \EmployeeSearchBasic();
                // $customerSearch->internalId = $internalIdSearchField;
                $customerSearch->entityId = $name;

                // creating request
                $request = new \SearchRequest();
                $request->searchRecord = $customerSearch;

                //make soap call of the created request
                $searchResponse = $service->search($request);
                if ($searchResponse->searchResult->status->isSuccess == 1 && $searchResponse->searchResult->totalRecords > 0) {
                    $record = $searchResponse->searchResult->recordList->record[0];
                    $phone = $record->phone;
                    if ($phone) {
                        $mcgAccountManager = $record->entityId;
                        $this->setCSMPhone($mcgAccountManager, $phone);
                    }
                }
            }
            die;
        } catch (\Exception $e) {
            print_r($e->getMessage());
            die('adfasdfasdf');
        }
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
        $internalIds = array();
        foreach ($results as $result) {
            $internalIds[] = $result['value'];
        }
        return $internalIds;
    }

    public function getMcgAccountManager()
    {
        $internalIds = $this->getCustomerInternalIds();
        $collection = $this->customerFactory->create()->getCollection()
            ->addAttributeToSelect("*")
            ->addFieldToFilter('ns_internal_id', ['in' => $internalIds])
            ->addFieldToFilter('csm_phone', ['null' => true])
            ->addFieldToFilter('msg_account_manager', ['neq' => 'NULL'])
            ->load();
        $mcgAccountManager = array();
        foreach ($collection as $col) {
            $mcgAccountManager[] = $col->getMsgAccountManager();
        }
        return array_unique($mcgAccountManager);
    }

    public function setCSMPhone($mcgAccountManager, $csm)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customers = $this->customerFactory->create()->getCollection()
            ->addAttributeToSelect("*")
            ->addFieldToFilter('csm_phone', ['null' => true])
            ->addFieldToFilter('msg_account_manager', ['eq' => $mcgAccountManager])
            ->load();
            echo "<pre>";
            // print_r($customers->getData());

        foreach ($customers as $customer) {
            $customerRepositoryInterface = $objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
            $cust = $customerRepositoryInterface->getById($customer->getId());
            $cust->setCustomAttribute('csm_phone', $csm);
            $customerRepositoryInterface->save($cust);
            print_r($customer->getData());
        }
    }
}
