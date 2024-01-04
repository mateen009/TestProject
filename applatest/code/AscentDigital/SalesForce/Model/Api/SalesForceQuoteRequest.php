<?php

namespace AscentDigital\SalesForce\Model\Api;

use Psr\Log\LoggerInterface;
use AscentDigital\SalesForce\Model\SalesForceFactory;
use AscentDigital\SalesForce\Model\ResourceModel\SalesForce;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ObjectManager;

use AscentDigital\SalesForce\Model\SalesForce as ModelSalesForce;

class SalesForceQuoteRequest
{
    protected $logger;
    protected $salesForceFactory;
    protected $SalesForce;
    private $resourceConnection;

    protected $resultFactory;
    protected $messageManager;


    public function __construct(
        LoggerInterface $logger,
        SalesForceFactory $salesForceFactory,
        SalesForce $salesForce,
        ResourceConnection $resourceConnection,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->logger = $logger;
        $this->salesForceFactory = $salesForceFactory;
        $this->salesForce = $salesForce;
        $this->resourceConnection = $resourceConnection;
        $this->resultFactory = $resultFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * @inheritdoc
     */

    public function getPost(
        $ATTUID__c,
        $Name,
        $Opp_Tracking_No__c,
        $Length_of_Demo__c,
        $Account_Name__c,
        $FirstNet_Type__c,
        $Discipline__c,
        $Customer_Email_Address__c,
        $Customer_Contact_Phone_Number__c,
        $Customer_Name__c,
        $Agency_Address_Street__c,
        $Agency_Address_City__c,
        $Agency_Address_State__c,
        $Agency_Zip_Code__c,
        $All_Rate_Plan_Quantities2__c,
        $CloseDate,
        $Anticipated_Demo_Start_Date__c,
        $Mtel_ID__c,
        $Id
    )
    {
        $data = [];
        $data['attuid'] = $ATTUID__c;
        $data['quote_name'] = $Name;
        $data['salesforce_opportunity_id'] = $Opp_Tracking_No__c;
        $data['demo_length'] = $Length_of_Demo__c == '30' || $Length_of_Demo__c == '45' || $Length_of_Demo__c == '60' || $Length_of_Demo__c == '61' ? $Length_of_Demo__c : '30';
        $data['agency_name'] = $Account_Name__c;
        $data['first_net_type'] = $FirstNet_Type__c;
        $data['discipline'] = $Discipline__c;
        $data['customer_email'] = $Customer_Email_Address__c;
        $data['customer_phone'] = $Customer_Contact_Phone_Number__c;
        $data['customer_name'] = $Customer_Name__c;
        $data['agency_street'] = $Agency_Address_Street__c;
        $data['agency_city'] = $Agency_Address_City__c;
        $data['agency_state'] = $Agency_Address_State__c;
        $data['agency_zipcode'] = $Agency_Zip_Code__c;
        $data['opportunity_size'] = $All_Rate_Plan_Quantities2__c;
        $data['opportunity_close_date'] = $CloseDate;
        $data['anticipated_demo_start_date'] = $Anticipated_Demo_Start_Date__c;
        $data['mtel_id'] = $Mtel_ID__c;
        $data['sf_id'] = $Id;
        $data['address_line_1'] = $Agency_Address_Street__c;
        $data['city'] = $Agency_Address_City__c;
        $data['state'] = $Agency_Address_State__c;
        $data['zipcode'] = $Agency_Zip_Code__c;
        $data['ship_to_contact'] = $Customer_Name__c;
        $data['shipping_phone'] = $Customer_Contact_Phone_Number__c;

        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $token = substr(str_shuffle($permitted_chars), 0, 20) . substr(str_shuffle($permitted_chars), 0, 20);
        $data['tracking_no'] = $token;
        try {
            $connection = $this->resourceConnection->getConnection();
            $tableName = $connection->getTableName('mobility_quote_sales_force_request');
            $connection->insert($tableName, $data);
            $response = array(
                'Mtel_ID__c' => $token
            );
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => $e->getMessage()];
            $this->logger->info($e->getMessage());
        }
        $returnArray = json_encode($response);

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/customQuote.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('MTIDB: ' . $token);
        if (isset($token) && !empty($ATTUID__c)) {
            $this->addQuote($token, $ATTUID__c);
        } else {
            throw new \Exception("Something went worong Or ATTUID__c field is empty.");
        }

        return $returnArray;
    }

    public function addQuote($mtelId, $ATTUID__c)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/customQuote.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('MTID: ' . $mtelId);
        $objectManager = ObjectManager::getInstance();
        $this->storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $this->salesForceFactory = $objectManager->get('\AscentDigital\SalesForce\Model\SalesForceFactory');
        $this->connection = $objectManager->create('Magento\Framework\App\ResourceConnection')->getConnection();
        $quoteFactory = $objectManager->get('Magento\Quote\Model\QuoteFactory');
        $this->_customerRepository = $objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
        $this->customerFactory = $objectManager->create('Magento\Customer\Model\CustomerFactory');
        $MtelId = $mtelId;
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();


        if (isset($MtelId)) {
            try {
                $collection = $this->salesForceFactory->create()->getCollection();
                $quoteData = $collection->addFieldToFilter('tracking_no', $MtelId)->addFieldToFilter('status', ['neq' => 'converted'])->getFirstItem();
                $customerCollection = $this->customerFactory->create()->getCollection();
                $customer = $customerCollection->addAttributeToFilter("attu_id", $ATTUID__c)->getFirstItem();
                $quoteFactoryCreate = $quoteFactory->create();
                $quoteCollection = $quoteFactoryCreate->getCollection();
                $customerId = $customer->getId();
                $customer = $this->_customerRepository->getById($customerId);
                echo $customerId;
                // $sm = $customer->getSalesManagerID();
                $sm_id = '';
                $tm_id = '';
                $em_id = '';
                $sm = $customer->getCustomAttribute('SalesManager_ID');
                if ($sm) {
                    if (!empty($sm->getValue())) {
                        $sm_id = $sm->getValue();
                    }
                }

                $tm = $customer->getCustomAttribute('TerritoryManager_ID');
                if ($tm) {
                    if (!empty($tm->getValue())) {
                        $tm_id = $tm->getValue();
                    }
                }

                $em = $customer->getCustomAttribute('Executive_ID');
                if ($em) {
                    if (!empty($em->getValue())) {
                        $em_id = $em->getValue();
                    }
                }


                $carts = $quoteCollection->addFieldToFilter('customer_id', $customerId)->addFieldToFilter('is_active', '1');
                foreach ($carts as $cart) {
                    $cart->setIsActive('0');
                    $cart->save();
                }
                // new empty cart creating
                $this->createCart($customerId, $customer, $quoteFactory, $this->storeManager, $quoteData, $sm_id, $tm_id, $em_id);
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        } else {
            throw new \Exception("Something went worong. Please try again!");
        }
    }

    public function createCart($customeId, $customer, $quoteFactory, $storeManager, $quoteData, $sm, $tm, $em)
    {
        $quote = $quoteFactory->create();
        $quote->setStoreId($storeManager->getStore()->getId());
        $quote->assignCustomer($customer);
        $quote->save();
        // form quote creating
        $this->createQuoteForm($quoteData, $quote->getId(), $customeId, $this->connection, $sm, $tm, $em);
    }

    public function createQuoteForm($quoteData, $quoteId, $customeId, $connection, $sm, $tm, $em)
    {
        $quoteFormData = (array) $quoteData->getData();
        $quoteFormData['quote_id'] = $quoteId;
        $quoteFormData['customer_id'] = $customeId;
        $quoteFormData['primary'] = $quoteFormData['first_net_type'] ? $quoteFormData['first_net_type'] : 'primary';
        $quoteFormData['created_at'] = date("Y-m-d H:i:s");
        $quoteFormData['updated_at'] = date("Y-m-d H:i:s");
        $quoteFormData['approval_1_id'] = $sm;
        $quoteFormData['approval_2_id'] = $tm;
        $quoteFormData['approval_3_id'] = $em;
        unset($quoteFormData['id']);
        unset($quoteFormData['opp_tracking_no']);
        unset($quoteFormData['account_name']);
        unset($quoteFormData['first_net_type']);
        unset($quoteFormData['all_rate_plan_quantities2']);
        unset($quoteFormData['close_date']);
        unset($quoteFormData['tracking_no']);
        unset($quoteFormData['mtel_id']);
        unset($quoteFormData['sf_id']);
        // echo "<pre>";print_r($quoteFormData);die;
        $tableName = 'mobility_quote_request';
        $connection->insert($tableName, $quoteFormData);
        $quoteData->setStatus('converted');
        $quoteData->save();
        $this->messageManager->addSuccess(__('New quote created successfully.'));
    }
}