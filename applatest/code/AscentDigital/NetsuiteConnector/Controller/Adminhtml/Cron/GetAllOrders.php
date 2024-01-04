<?php

namespace AscentDigital\NetsuiteConnector\Controller\Adminhtml\Cron;

use Magento\Framework\Filesystem\DirectoryList as Directory;
use AscentDigital\NetsuiteConnector\Helper\OrderHelper;
use Magento\Directory\Model\Region;
use Magento\Framework\App\ResourceConnection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use AscentDigital\NetsuiteConnector\Model\NSCronFactory;

class GetAllOrders extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var Directory
     */
    protected $directory;

    protected $helper;

    protected $regionFactory;
    protected $nsCron;
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;


    /**
     * @param \Magento\Framework\App\Action\Context $context,
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        CollectionFactory $customerCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        Directory $directory,
        OrderHelper $helper,
        NSCronFactory $nsCron,
        Region $regionFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->_orderRepository = $orderRepository;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->directory = $directory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->productFactory = $productFactory;
        $this->helper = $helper;
        $this->nsCron = $nsCron;
        $this->regionFactory = $regionFactory;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context);
    }

    /**
     * Get all Orders Controller
     */
    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/customOrderCron.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('Get All Orders Cron Is Executed');
        $root = $this->directory->getRoot();
        require_once($root . '/lib/PHPToolkit/NetSuiteService.php');

        $service = new \NetSuiteService();
        $service->setSearchPreferences(false, 20);
        $service->setPreferences(false, true);
        $cron = $this->nsCron->create()->load('get_all_orders_cron', 'title');
        if ($cron->getData()) {
            // if (0) {
            if ($cron->getIndex() > 1) {
                $SearchMoreWithIdRequest = new \SearchMoreWithIdRequest();
                // assigning search id 
                $SearchMoreWithIdRequest->searchId = $cron->getSearchId();

                //assigning next page index
                // $SearchMoreWithIdRequest->pageIndex = 4;
                $SearchMoreWithIdRequest->pageIndex = $cron->getIndex();

                // search next page result on the basis of search id
                $searchResponse = $service->searchMoreWithId($SearchMoreWithIdRequest);
                $searchResult = $searchResponse->searchResult;
                $totalPages = $searchResult->totalPages;
                $pageIndex = $searchResponse->searchResult->pageIndex;
                if ($pageIndex < $totalPages) {
                    $cron->setIndex($pageIndex + 1);
                    $cron->setTotalPages($totalPages);
                    $cron->setTitle('get_all_orders_cron');
                    $cron->setSearchId($searchResult->searchId);
                    $cron->save();
                } else {
                    $cron->setIndex(0);
                    $cron->setTotalPages('');
                    $cron->setTitle('get_all_orders_cron');
                    $cron->setSearchId('');
                    $cron->save();
                }
            } else {
                $searchResponse = $this->searchOrdersInNetsuite($service, $cron);
            }
        } else {
            $searchResponse = $this->searchOrdersInNetsuite($service, $cron);
        }
        $this->processSearchResponse($searchResponse, $logger);

        // $this->getAllOrders();
    }

    public function searchOrdersInNetsuite($service, $cron)
    {
        $customerInternalIds = $this->getCustomerInternalIds();
        $internalIds = array();
        foreach ($customerInternalIds as $customerInternalId) {
            $internalId = new \RecordRef();
            $internalId->internalId = $customerInternalId['value'];;
            // $internalId->internalId = '42346';
            $internalIds[] = $internalId;
        }
        // customer search condition
        $internalIdSearchField = new \SearchMultiSelectField();
        $internalIdSearchField->operator = "anyOf";
        $internalIdSearchField->searchValue = $internalIds;

        // type search condition 
        $type = new \SearchEnumMultiSelectField();
        $type->searchValue = array('_salesOrder');
        $type->operator = 'anyOf';
        $search = new \TransactionSearchBasic();
        $search->type = $type;
        $search->entity = $internalIdSearchField;


        // $search->entity = 

        $request = new \SearchRequest();
        $request->searchRecord = $search;

        $searchResponse = $service->search($request);
        $searchResult = $searchResponse->searchResult;
        $totalPages = $searchResult->totalPages;
        $pageIndex = $searchResponse->searchResult->pageIndex;
        if ($totalPages > 1) {
            if ($cron->getData()) {
                $cron->setIndex($pageIndex + 1);
                $cron->setTotalPages($totalPages);
                $cron->setTitle('get_all_orders_cron');
                $cron->setSearchId($searchResult->searchId);
                $cron->save();
            } else {
                $cron = $this->nsCron->create();
                $cron->setIndex($pageIndex + 1);
                $cron->setTotalPages($totalPages);
                $cron->setTitle('get_all_orders_cron');
                $cron->setSearchId($searchResult->searchId);
                $cron->save();
            }
        }
        return $searchResponse;
    }

    public function processSearchResponse($searchResponse, $logger)
    {
        // Loop through the results and extract the sales order numbers
        if (!$searchResponse->searchResult->status->isSuccess) {
            echo "<pre>";
            print_r($searchResponse);
        } else {

            $this->createOrder($searchResponse);
        }
        $logger->info('Get All Orders Cron Is Finished');
    }
    public function createOrder($searchResponse)
    {

        // print_r($searchResponse);die('mateen');
        $response = $searchResponse->searchResult->recordList->record;
        //$response = (array)$searchResponse;

        foreach ($response as $res) {
            $orders = $this->_orderCollectionFactory->create()
                ->addFieldToFilter('ns_internal_id', $res->internalId);
            //check on internal id if already exist
            if (!$orders->getData()) {
                $orderInfo = array();
                $address = array();
                $items = array();
                $result = (array) $res;
                $regionId = 0;


                $currency = $result['currency'] ?: 'USD';
                $internalId = $result['entity']->internalId ?? null;
                $orderInfo['customer_internal_id'] = $internalId;
                $customers = $this->customerCollectionFactory->create();
                $customers->addFieldToFilter('ns_internal_id', $internalId);
                $customer = $customers->getFirstItem();

                if (!$customer->getId()) {
                    continue;
                }

                $email = 'test@mailinator.com';
                $address['firstname'] = $result['shippingAddress']->addressee ?? null;
                $address['lastname'] = $result['shippingAddress']->addressee ?? null;
                $address['street'] = $result['shippingAddress']->addr1 ?? null;
                $address['city'] = $result['shippingAddress']->city ?? null;
                $regionCode = $result['shippingAddress']->state ?? null;
                $countryCode = 'US';
                if ($regionCode) {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $regionCollectionFactory = $objectManager->get('Magento\Directory\Model\ResourceModel\Region\CollectionFactory');
                    $region = $regionCollectionFactory->create()
                        ->addFieldToFilter(['code', 'name'], [['eq' => $regionCode], ['eq' => $regionCode]])
                        ->getFirstItem();
                    $regionId = $region['region_id'];
                }
                $address['country_id'] = $countryCode; //$result['country']->country;
                $address['region'] = $result['shippingAddress']->state ?? null;
                $address['region_id'] = $regionId; //$result['shippingAddress']->state;
                $address['postcode'] = $result['shippingAddress']->zip ?? null;
                $address['telephone'] = $result['shippingAddress']->addrPhone ?? $result['billingAddress']->addrPhone ?? null;

                $address['fax'] = ''; //$result['shippingAddress']->addressee;
                $address['save_in_address_book'] = 0;

                $orderInfo['currency_id'] = $currency;
                $orderInfo['email'] = $email;
                $orderInfo['address'] = $address;
                if ($result['paymentMethod']) {
                    $orderInfo['payment_method'] = $result['paymentMethod'];
                }
                if ($result['shipMethod']) {
                    $orderInfo['ship_method'] = $result['shipMethod'];
                }

                //ns_so_number // ns_internal_id
                $orderInfo['ns_so_number'] = $result['tranId'];
                $orderInfo['ns_internal_id'] = $result['internalId'];
                $itemList = (array) $result['itemList']->item;
                foreach ($itemList as $item) {
                    $_item = (array) $item;
                    $product = $this->productFactory->create()->loadByAttribute('item_ns_internal_id', $_item['item']->internalId);
                    if ($product) {
                        $items['qty'] = $_item['quantity'];
                        $items['product_id'] = $product->getEntityId(); //'SM-T540NZKAXAR';//$item->SalesOrderItem->quantity;
                        $orderInfo['items'][] = $items;
                    }
                }


                $this->helper->createOrder($orderInfo, $customer);
            }
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
        return $results;
    }
}
