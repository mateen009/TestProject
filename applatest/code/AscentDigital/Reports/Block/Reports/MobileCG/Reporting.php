<?php

namespace AscentDigital\Reports\Block\Reports\MobileCG;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;

class Reporting extends \Magento\Framework\View\Element\Template
{
    protected $_orderCollectionFactory;
    protected $salesRep;
    protected $_productCollectionFactory;
    protected $storeManager;
    protected $stockItemRepository;
    protected $locationCollection;
    protected $resourceConnection;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Sales\Model\Order\ItemFactory $itemCollectionFactory,
        \AscentDigital\Reports\Model\SalesReps $salesRep,
        \Mobility\QuoteRequest\Model\QuoteRequestFactory $quoteRequestFactory,
        \AscentDigital\NetsuiteConnector\Model\ResourceModel\Collection $locationCollection,
        StockRegistryInterface $stockRegistry,
        StockItemRepository $stockItemRepository,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Customer\Model\Session $customerSession
    )
    {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->storeManager = $storeManager;
        $this->_customerFactory = $customerFactory;
        $this->_itemCollectionFactory = $itemCollectionFactory;
        $this->salesRep = $salesRep;
        $this->_quoteRequestFactory = $quoteRequestFactory;
        $this->_customerSession = $customerSession;
        $this->locationCollection = $locationCollection;
        $this->stockRegistry = $stockRegistry;
        $this->stockItemRepository = $stockItemRepository;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context);
    }

    public function getLocationInventoryReport(){
      
        $customerId = $this->_customerSession->getCustomer()->getId(); 
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerData = $objectManager->create('Magento\Customer\Model\Customer')->load($customerId); 
        
        $locationId = $customerData->getLocationId(); 
        return $this->locationCollection->addFieldToFilter('location_id', array('in'=> $locationId))->setPageSize(3);
         
     }

    public function getInventoryReport(){
        $_storeId = $this->storeManager->getStore()->getId();
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addStoreFilter($_storeId);
        $collection->setPageSize(3);
        $productsData = array();

        foreach($collection as $product) {
          $sku = $product->getSku();
          $pid = $product->getId();
          $pName = $product->getName();
          $_salableQty = $this->getStockItemData($pid);
          $productsData[$pid]['sku'] = $sku;
          $productsData[$pid]['name'] = $pName;
          $productsData[$pid]['qty'] = $_salableQty;
        }
        return $productsData;
     }

    public function getOrderTypeReports(){
        
        $ordersByType = array();
        $ordersByType['new order'] = $this->getNewOrderCollectionByType('new order');
        $ordersByType['installation'] = $this->getNewOrderCollectionByType('installation');
        $ordersByType['return'] = $this->getNewOrderCollectionByType('return');
        $ordersByType['deployment'] = $this->getNewOrderCollectionByType('deployment');
        return $ordersByType;
    }


    public function getAeYtdReport()
    {
        $ordersByType = array();
        $ordersByType['new order'] = $this->getOrderQtyByType('new order');
        $ordersByType['installation'] = $this->getOrderQtyByType('installation');
        $ordersByType['return'] = $this->getOrderQtyByType('return');
        $ordersByType['deployment'] = $this->getOrderQtyByType('deployment');
        return $ordersByType;

    }

    public function getAeYtdReportByType()
    {
      if(isset($_GET['otype'])) {
        $type = $_GET['otype'];
        $collection = $this->_orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('order_type', $type)->setPageSize(3);
        return $collection;
      } else {
        return '';
      }
    }

    public function getRepairReport(){
        $query = 'SELECT ri.*, r.title as reason, ic.title as conditionTitle, soi.name as productName, s.title as statusTitle, re.created_at, 
                    re.customer_name FROM `amasty_rma_request_item` ri 
                   LEFT JOIN `amasty_rma_reason` r ON ri.reason_id = r.reason_id
                   LEFT JOIN `amasty_rma_item_condition` ic ON ri.condition_id = ic.condition_id
                   LEFT JOIN `sales_order_item` soi ON ri.order_item_id = soi.item_id
                   LEFT JOIN `amasty_rma_status` s ON ri.item_status = s.status_id
                   LEFT JOIN `amasty_rma_request` re ON ri.request_id = re.request_id
                   WHERE ri.reason_id = 10 LIMIT 3';
          $data = $this->resourceConnection->getConnection()->fetchAll($query);
         return $data;
    }
     

    public function getOrderQtyByType($type)
    {
        $collection = $this->_orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('order_type', $type)->setPageSize(3);
        return count($collection); 
    }

     public function getStockItemData($productId)
     {
        $productStock = $this->stockRegistry->getStockItem($productId);
        return $productStock->getQty();
     }

     public function getStockItem($productId)
     {
        $productStock = $this->stockItemRepository->get($productId);
        return $productStock->getQty();
     }
 
     public function getProductName($productId) {
         $pName="";
         $collection = $this->_productCollectionFactory->create();
         $collection->setPageSize(3);
         $collection->addAttributeToSelect('*')
         ->addFieldToFilter('entity_id', array('in'=> $productId));
 
         foreach($collection as $product) {
             $pName = $product->getName();
         }
          return $pName;
     }

     public function getNewOrderCollectionByType($type)
    {
        $totalQty = 0;
        $qtyArray = array();
        $collection = $this->_orderCollectionFactory->create()
        ->addAttributeToSelect('*')->addFieldToFilter('order_type', $type)->setPageSize(3);
        $totalOrders = count($collection);
        foreach($collection as $order) {
            $totalQty += $order->getTotalQtyOrdered();
        }
        $qtyArray['totalOrders'] = $totalOrders;
        $qtyArray['totalQty'] = $totalQty;
        return $qtyArray;

    }
    

}