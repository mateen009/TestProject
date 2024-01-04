<?php

namespace AscentDigital\NetsuiteConnector\Cron\Customer;

use Magento\Framework\Filesystem\DirectoryList as Directory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\GroupFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Api\ScopedProductTierPriceManagementInterface;
use Magento\Framework\App\ResourceConnection;
use Custom\AdvanceExchange\Model\McgSkuFactory;
use AscentDigital\NetsuiteConnector\Model\NSCronFactory;

class CustomerSpecificPrices
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
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $product;

    /**
     * @var \Magento\Customer\Model\GroupFactory
     */
    protected $groupFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    // protected $logger;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $directory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var ScopedProductTierPriceManagementInterface
     */
    private $tierPrice;

    /**
     * @var ProductTierPriceInterfaceFactory
     */
    private $productTierPriceFactory;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     *  @var \Custom\AdvanceExchange\Model\McgSkuFactory;
     */
    protected $mcgSkuFactory;

    /**
     * @var \AscentDigital\NetsuiteConnector\Model\NSCronFactory
     */
    protected $nsCron;

    /**
     * @param \Magento\Framework\App\Action\Context $context,
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        ProductFactory $product,
        GroupFactory $groupFactory,
        Directory $directory,
        CustomerFactory $customerFactory,
        ScopedProductTierPriceManagementInterface $tierPrice,
        ProductTierPriceInterfaceFactory $productTierPriceFactory,
        ResourceConnection $resourceConnection,
        McgSkuFactory $mcgSkuFactory,
        NSCronFactory $nsCron
    ) {
        $this->_orderRepository = $orderRepository;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->product = $product;
        $this->groupFactory = $groupFactory;
        $this->directory = $directory;
        $this->customerFactory = $customerFactory;
        $this->tierPrice = $tierPrice;
        $this->productTierPriceFactory = $productTierPriceFactory;
        $this->resourceConnection = $resourceConnection;
        $this->mcgSkuFactory = $mcgSkuFactory;
        $this->nsCron = $nsCron;
    }

    /**
     * Test Order Create Shipment Controller
     */
    public function execute()
    {
        // $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/crontest.log');
        // $logger = new \Zend_Log();
        // $logger->addWriter($writer);
        // $logger->info('CustomerSpecificPrices cron is running');
        // die('cron');
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/netsuite_cron.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info("Customer Specific Prices cron is executed.");
        //sending email to the customer
        $to = "yasirpayee02@gmail.com";
        $subject = "Customer Specific Prices cron";
        
        $message = "Customer Specific Prices cron run Successfully";
        
        
        $email = mail($to, $subject, $message);
        $root = $this->directory->getRoot();
        require_once($root . '/lib/PHPToolkit/NetSuiteService.php');
        $service = new \NetSuiteService();
        $service->setSearchPreferences(false, 50);

        $cron = $this->nsCron->create()->load('customer_specific_prices_cron', 'title');
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
                    $cron->setTitle('customer_specific_prices_cron');
                    $cron->setSearchId($searchResult->searchId);
                    $cron->save();
                } else {
                    $cron->setIndex(0);
                    $cron->setTotalPages('');
                    $cron->setTitle('customer_specific_prices_cron');
                    $cron->setSearchId('');
                    $cron->save();
                }
            } else {
                $searchResponse = $this->searchPricesInNetuite($service, $cron);
            }
        } else {
            $searchResponse = $this->searchPricesInNetuite($service, $cron);
        }
        $this->processSearchResponse($searchResponse, $logger, $root);
        $logger->info("Customer Specific Prices cron is finished.");
    }

    public function searchPricesInNetuite($service, $cron)
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
        // customer search 
        $customerSearch = new \CustomerSearchBasic();
        $customerSearch->internalId = $internalIdSearchField;
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
                $cron->setTitle('customer_specific_prices_cron');
                $cron->setSearchId($searchResult->searchId);
                $cron->save();
            } else {
                $cron = $this->nsCron->create();
                $cron->setIndex($pageIndex + 1);
                $cron->setTotalPages($totalPages);
                $cron->setTitle('customer_specific_prices_cron');
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
                $logger->debug('AscentDigital\NetsuiteConnector\Cron\Customer\CustomerSpecificPrices ' . $message->message);
            }
        } else {
            // echo "<pre><br>SEARCH SUCCESS, records found: " . $searchResponse->searchResult->totalRecords;
            $customers = $searchResponse->searchResult->recordList->record;
            foreach ($customers as $customer) {
                $internalId = $customer->internalId;
                $itemPricingList = $customer->itemPricingList;
                if ($itemPricingList && $itemPricingList->itemPricing) {
                    $itemPricingList = $itemPricingList->itemPricing;
                    $customerInternalId = $customer->internalId;
                    $groupId = $this->getGroupId($customerInternalId);
                    // adding customer group price
                    if (!empty($groupId)) {
                        foreach ($itemPricingList as $itemPricing) {
                            $itemInternalId = $itemPricing->item->internalId;
                            $price = $itemPricing->price;
                            $this->addCustomerGroupPricing($groupId, $itemInternalId, $price, $internalId, $logger);
                        }
                    } else {
                        $logger->debug(__('AscentDigital\NetsuiteConnector\Cron\Customer\CustomerSpecificPrices: ' . 'Something went wrong while adding customer group pricing.'));
                    }
                }
            }
        }
    }

    /**
     * getGroupId
     * @param internalId
     * get group id by netsuite customer internal id
     * return group id
     */
    public function getGroupId($internalId)
    {
        $group = $this->groupFactory->create();
        $group->load($internalId, 'customer_group_code');
        $groupId = $group->getId();
        // if group is not exist 
        if (empty($groupId)) {
            // creating group 
            $group = $this->groupFactory->create();
            $group
                ->setCode($internalId)
                ->setTaxClassId(3)
                ->save();
            $groupId = $group->getId();
        }
        return $groupId;
    }

    /**
     * addCustomerGroupPricing($groupId, $itemInternalId, $price)
     * @param $groupId
     * @param $itemInternalId
     * @param $price
     * set customer group price by netsuite item internal id
     * return 
     */
    public function addCustomerGroupPricing($groupId, $itemInternalId, $itemPrice, $internalId, $logger)
    {
        $productCollection = $this->product->create()->getCollection();
        // get product by NS internal id
        $product = $productCollection->addFieldToFilter('item_ns_internal_id', $itemInternalId)->getFirstItem();
        if ($product->getData() && !empty($product->getSku())) {
            $qty = 1.00; //must be float value.
            $itemPrice = $itemPrice < 1 ? 1 : $itemPrice;
            $price = floatval($itemPrice); //must be float value.
            $customerGroupId = $groupId;
            $sku = $product->getSku();
            $product_id = $product->getId();
            try {
                $tierPriceData = $this->productTierPriceFactory->create();
                $tierPriceData->setCustomerGroupId($customerGroupId)
                    ->setQty($qty)
                    ->setValue($price);
                // add tier price 
                $tierPrice = $this->tierPrice->add($sku, $tierPriceData);
                $customers = $this->getCustomers($internalId);
                foreach ($customers as $customer) {
                    $msgFactory = $this->mcgSkuFactory->create();
                    $msgFactory->setProductId($product_id);
                    $msgFactory->setCustomerId($customer->getId());
                    $msgFactory->setSku($sku);
                    $msgFactory->save();
                }
            } catch (\NoSuchEntityException $exception) {
                $logger->debug(__('AscentDigital\NetsuiteConnector\Cron\Customer\CustomerSpecificPrices: ' . $exception->getMessage()));
            } catch (\Exception $e) {
                $logger->debug(__('AscentDigital\NetsuiteConnector\Cron\Customer\CustomerSpecificPrices: ' . $e->getMessage()));
            }
        } else {
            $logger->debug(__('AscentDigital\NetsuiteConnector\Cron\Customer\CustomerSpecificPrices: ' . 'No such entity exist with sku: sku'));
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

    /**
     * getCustomerIds
     * get customer ids from magento
     * return 
     */
    public function getCustomers($internalId)
    {
        $ids = $this->customerFactory->create()->getCollection()
            ->addAttributeToSelect("entity_id")
            ->addAttributeToFilter("ns_internal_id", $internalId)
            ->load();
        return $ids;
    }
}


// $qty = 1.00;//must be float value.
// $price = 50.00;//must be float value.
// $customerGroupId = 2;
// $sku = 'FN6317A';
// try {
//     $tierPriceData = $this->productTierPriceFactory->create();
//     $tierPriceData->setCustomerGroupId($customerGroupId)
//         ->setQty($qty)
//         ->setValue($price);
//     $tierPrice = $this->tierPrice->add($sku, $tierPriceData);
// } catch (NoSuchEntityException $exception) {
//     throw new NoSuchEntityException(__($exception->getMessage()));
// }

// SELECT *
// FROM `customer_entity_varchar`
// WHERE attribute_id = 175
// GROUP BY value