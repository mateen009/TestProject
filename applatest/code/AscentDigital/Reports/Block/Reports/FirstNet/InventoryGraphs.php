<?php

namespace AscentDigital\Reports\Block\Reports\FirstNet;

use \Magento\Framework\App\ObjectManager;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\Customer\Model\CustomerFactory;

class InventoryGraphs extends \Magento\Framework\View\Element\Template
{
    
    protected $_orderCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected $orders;

    /**
     * @var CollectionFactoryInterface
     */
    private $orderCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Config\Source\Order\Status
     */
    protected $orderStatus;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    protected $exportHelper;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    protected $_urlInterface;

    protected $customerFactory;
    
    protected $salesRep;
    protected $itemCollectionFactory;
    protected $_productCollectionFactory;

    
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\Order\ItemFactory $itemCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,

        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Sales\Model\Config\Source\Order\Status $orderStatus,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \AscentDigital\Reports\Model\SalesReps $salesRep,
        \AscentDigital\Reports\Helper\ExportTotalSalesOrderReport $exportHelper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\UrlInterface $urlInterface,
        CustomerFactory $customerFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_productCollectionFactory = $productCollectionFactory;

        $this->_customerSession = $customerSession;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->_orderConfig = $orderConfig;
        $this->orderStatus = $orderStatus;
        $this->exportHelper = $exportHelper;
        $this->_storeManager = $storeManager;
         $this->salesRep = $salesRep;
        $this->request = $request;
        $this->_urlInterface = $urlInterface;
        $this->customerFactory = $customerFactory;
    }

    // public function getOrderCount($sku){
    //     $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    //     $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
    //     $storeId = $storeManager->getStore()->getStoreId();
        
    //     if (!($customerId = $this->_customerSession->getCustomerId())) {
    //         return false;
    //     }
    //     $this->orders = $this->_orderCollectionFactory->create($this->salesRep->getManagerReps($customerId))->addAttributeToSelect('*')->addFieldToFilter(
    //         'status',
    //         array("in",'Shipping','Processing','Complete')
    //     )->addFieldToFilter('main_table.store_id', $storeId);
    //     $this->orders->getSelect()->join(array('order_item' => 'sales_order_item'),'main_table.entity_id = order_item.order_id');
    //     $this->orders->addFieldToFilter('order_item.sku', array(array('like' => '%'.$sku.'%')));
    //      $this->orders->getSelect()->group('main_table.entity_id');
    //     $count = count($this->orders);
    //     return $count;
    // }
    
    // public function getDueQty($sku){
        
    //     $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    //     $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
    //     $storeId = $storeManager->getStore()->getStoreId();
        
    //     $orderToDate = date('Y-m-d H:i:s');
    //     if (!($customerId = $this->_customerSession->getCustomerId())) {
    //         return false;
    //     }
    //     $totalQtyOrdered = 0 ;
    //     $orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addAttributeToSelect('*')->addFieldToFilter(
    //         'status',
    //         array("in",'Shipping')
    //     )->addFieldToFilter('main_table.store_id', $storeId);
    //     $orders->getSelect()->join(array('order_item' => 'sales_order_item'),'main_table.entity_id = order_item.order_id');
    //     $orders->addExpressionFieldToSelect('order_item.quantity_ordered', 'SUM({{qty_ordered}})', 'qty_ordered')->addFieldToFilter('order_item.sku', array(array('like' => '%'.$sku.'%')))->addAttributeToFilter('due_date', array('from' => '2020-10-12 09:20:25', 'to' => $orderToDate))->addAttributeToFilter('rma_return_status',['in' => 'no']);
    //     $orders->getSelect()->group('order_item.sku');
    //     foreach($orders as $order){
    //         $totalQtyOrdered= $order['order_item.quantity_ordered'];
    //     }
    //     return $totalQtyOrdered;
        
    // }
    
    // public function getTotalOrderQty($sku){
        
    //     $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    //     $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
    //     $storeId = $storeManager->getStore()->getStoreId();
        
        
    //     $orderToDate = date('Y-m-d H:i:s');
    //     if (!($customerId = $this->_customerSession->getCustomerId())) {
    //         return false;
    //     }
    //     $totalQtyOrdered = 0 ;
    //     $orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addAttributeToSelect('*')->addFieldToFilter(
    //         'status',
    //         array("in",'Shipping','Processing','Complete')
    //     )->addFieldToFilter('main_table.store_id', $storeId);
    //     $orders->getSelect()->join(array('order_item' => 'sales_order_item'),'main_table.entity_id = order_item.order_id');
    //     $orders->addFieldToFilter('order_item.sku', array(array('like' => '%'.$sku.'%')));
    //     $orders->addExpressionFieldToSelect('order_item.quantity_ordered', 'SUM({{qty_ordered}})', 'qty_ordered');
    //     $orders->getSelect()->group('order_item.sku');
    //     foreach($orders as $order){
    //         $totalQtyOrdered+= $order['order_item.quantity_ordered'];
    //     }
    //     return $totalQtyOrdered;
        
        
        
    // }
    
    // public function getReturnedQty($sku){
        
    //     $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    //     $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
    //     $storeId = $storeManager->getStore()->getStoreId();
        
    //     if (!($customerId = $this->_customerSession->getCustomerId())) {
    //         return false;
    //     }
    //     $totalQtyOrdered = 0 ;
    //     $orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addAttributeToSelect('*')->addFieldToFilter(
    //         'status',
    //         array("in",'Shipping','Complete')
    //     )->addFieldToFilter('main_table.store_id', $storeId);
    //     $orders->getSelect()->join(array('order_item' => 'sales_order_item'),'main_table.entity_id = order_item.order_id');
    //     $orders->addExpressionFieldToSelect('order_item.quantity_ordered', 'SUM({{qty_ordered}})', 'qty_ordered')->addFieldToFilter('order_item.sku', array(array('like' => '%'.$sku.'%')))->addAttributeToFilter('rma_return_status',['in' => 'yes']);
    //     $orders->getSelect()->group('order_item.sku');
    //     foreach($orders as $order){
    //         $totalQtyOrdered= $order['order_item.quantity_ordered'];
    //     }
    //     return $totalQtyOrdered;
    // }
    
    // public function getDemoQty($sku){
        
    //     $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    //     $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
    //     $storeId = $storeManager->getStore()->getStoreId();
        
    //     if (!($customerId = $this->_customerSession->getCustomerId())) {
    //         return false;
    //     }
    //     $totalQtyOrdered = 0 ;
    //     $orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addAttributeToSelect('*')->addFieldToFilter(
    //         'status',
    //         array("in",'Shipping')
    //     )->addFieldToFilter('main_table.store_id', $storeId);
    //     $orders->getSelect()->join(array('order_item' => 'sales_order_item'),'main_table.entity_id = order_item.order_id');
    //     $orders->addExpressionFieldToSelect('order_item.quantity_ordered', 'SUM({{qty_ordered}})', 'qty_ordered')->addFieldToFilter('order_item.sku', array(array('like' => '%'.$sku.'%')))->addAttributeToFilter('rma_return_status',['in' => 'no']);
    //     $orders->getSelect()->group('order_item.sku');
    //     foreach($orders as $order){
    //         $totalQtyOrdered= $order['order_item.quantity_ordered'];
    //     }
    //     return $totalQtyOrdered;
    // }
    
    

    // private function getOrderCollectionFactory()
    // {
    //     if ($this->orderCollectionFactory === null) {
    //         $this->orderCollectionFactory = ObjectManager::getInstance()->get(CollectionFactoryInterface::class);
    //     }
    //     return $this->orderCollectionFactory;
    // }

    public function getEmptyOrdersMessage()
    {
        return __('You have placed no orders or no any selected order status found.');
    }
    
    public function getCustomerType()
    {
        $this->_customerSession->getCustomerType();
    }

    // public function getOrders(){
    //     $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    //     $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
    //     if (!($customerId = $this->_customerSession->getCustomerId())) {
    //         return false;
    //     }
    //     $storeId = $storeManager->getStore()->getStoreId();
    //     $orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addAttributeToSelect('*')->addFieldToFilter(
    //     'status',array("in",'Shipping','Processing','Complete'))->addFieldToFilter('main_table.store_id', $storeId);
    //     $orders->getSelect()->join(array('order_item' => 'sales_order_item'),'main_table.entity_id = order_item.order_id');
    //     $orders->getSelect()->group('order_item.sku');
        
    //     return $orders;
        
    // }
    // public function getCustomerType(){
    //     return $this->_customerSession->getCustomerType();
    // }


public function getProductCollection() {
    $_storeId = 2; // firstnet
    $collection = $this->getItemCollection();
    $collection->addAttributeToSelect('product_id');
    $collection->addAttributeToSelect('name');
    $collection->addAttributeToSelect('sku');
    $collection->getSelect()->join(
        array('order' => $collection->getTable('sales_order')),
        'main_table.order_id = order.entity_id and order.status in ("shipping", "complete", "processing")',
        array('customer_email')
    )
        ->columns(array('total_orders' => new \Zend_Db_Expr('COUNT(main_table.item_id)')))
        ->columns(array('total_qty' => new \Zend_Db_Expr('ROUND(SUM(main_table.qty_ordered))')))
        ->group('sku');
    $collection->addAttributeToFilter('order.store_id', $_storeId);
//    echo "<pre>";print_r($collection->getData());die("error");
    return $collection;
}

public function getProducts()
{
    $collection = $this->getProductCollection();   
     $email = $this->request->getParam('email');
    $customerType = $this->_customerSession->getCustomerType();
    $customerEmail = $this->_customerSession->getCustomer()->getEmail();
    // $customerEmail = $this->_customerSession->getCustomer()->getEmail();
    // echo "<pre>";print_r( $email);die;
    if ($customerType == '1') {
        $collection->addAttributeToFilter('order.customer_email', array('like' => '%' . trim($customerEmail) . '%'));
    }
    $sm_email = $this->request->getParam('sm_email');
    if ($customerType == '3') {
        $collection->addAttributeToFilter('order.sm_email', array('like' => '%' . trim($customerEmail) . '%'));
    }
    $tm_email = $this->request->getParam('tm_email');
    if ($customerType == '4') {
        $collection->addAttributeToFilter('order.tm_email', array('like' => '%' . trim($customerEmail) . '%'));
    }
    $em_email = $this->request->getParam('em_email');
    if ($customerType == '5') {
        $collection->addAttributeToFilter('order.em_email', array('like' => '%' . trim($customerEmail) . '%'));
    }
    // generate_pdf
    // $generate_pdf = $this->request->getParam('generate_pdf');
    // if (isset($generate_pdf)) {
    //     $this->generatePDF($collection);
    // }
    
   

    // echo "<pre>";
    //  print_r($collection->getData());
    // die;
   

    // die('sdfs');
    return $collection;
}

public function getProductSkus($skus)
{
    // print_r($email);die;
    $_storeId = $this->_storeManager->getStore()->getId();
    $_storeId = 2; //firstnet

    // $collection = $this->getItemCollection();
    $collection = $this->_productCollectionFactory->create();
    $collection->addAttributeToSelect('*')
    ->addFieldToFilter('sku', array('in'=> $skus))
    ;
    $collection->addStoreFilter($_storeId);

    $productsData = array();

    foreach ($collection as $product) {
        $sku = $product->getSku();
        $pid = $product->getId();
        $pName = $product->getName();
        $productsData[$pid]['sku'] = $sku;
        $productsData[$pid]['name'] = $pName;
        //echo $pName." : ".$sku."<br/>";
    }

    //  echo "<pre>";print_r($productsData);die();
    return $productsData;
}
 public function getOrderCount($sku){
        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        // $storeId = $storeManager->getStore()->getStoreId();
        
        // if (!($customerId = $this->_customerSession->getCustomerId())) {
        //     return false;
        // }
        // $this->orders = $this->_orderCollectionFactory->create($this->salesRep->getManagerReps($customerId))->addAttributeToSelect('*')->addFieldToFilter(
        //     'status',
        //     array("in",'Shipping','Processing','Complete')
        // )->addFieldToFilter('main_table.store_id', $storeId);
        // $this->orders->getSelect()->join(array('order_item' => 'sales_order_item'),'main_table.entity_id = order_item.order_id');
        // $this->orders->addFieldToFilter('order_item.sku', array(array('like' => '%'.$sku.'%')));
        //  $this->orders->getSelect()->group('main_table.entity_id');
        $customerType = $this->_customerSession->getCustomerType();
        $customerEmail = $this->_customerSession->getCustomer()->getEmail();
        $collection = $this->getItemCollection();
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToFilter('sku', $sku);
        //$collection->addAttributeToFilter('rma_return_status', 'no');
        $collection->getSelect()->join(
            array('order' => $collection->getTable('sales_order')),
            'main_table.order_id = order.entity_id 
            and order.status = "shipping" ',
            array('entity_id')
        )
            ->columns(array('total_qty' => new \Zend_Db_Expr('ROUND(SUM(main_table.qty_ordered))')))
            ->group('sku');
        $email = $this->request->getParam('email');
        if ($customerType == '1') {
            $collection->addAttributeToFilter('order.customer_email', array('like' => '%' . trim($customerEmail) . '%'));
        }
        $sm_email = $this->request->getParam('sm_email');
        if ($customerType == '3') {
            $collection->addAttributeToFilter('order.sm_email', array('like' => '%' . trim($customerEmail) . '%'));
        }
        $em_email = $this->request->getParam('em_email');
        if ($customerType == '4') {
            $collection->addAttributeToFilter('order.em_email', array('like' => '%' . trim($customerEmail) . '%'));
        }
        $em_email = $this->request->getParam('em_email');
        if ($customerType == '5') {
            $collection->addAttributeToFilter('order.em_email', array('like' => '%' . trim($customerEmail) . '%'));
        }
        $count = count($collection->getData());
        return $count;
    }

public function getTotalQty($sku)
{
    $date = date('Y-m-d');
    $customerType = $this->_customerSession->getCustomerType();
    $customerEmail = $this->_customerSession->getCustomer()->getEmail();

    $collection = $this->getItemCollection();
    $collection->addAttributeToSelect('sku');
    $collection->addAttributeToFilter('sku', $sku);
    //$collection->addAttributeToFilter('rma_return_status', 'no');
    $collection->getSelect()->join(
        array('order' => $collection->getTable('sales_order')),
        'main_table.order_id = order.entity_id 
        and order.status = "shipping" ',
        array('entity_id')
    )
        ->columns(array('total_qty' => new \Zend_Db_Expr('ROUND(SUM(main_table.qty_ordered))')))
        ->group('sku');
    $email = $this->request->getParam('email');
    if ($customerType == '1') {
        $collection->addAttributeToFilter('order.customer_email', array('like' => '%' . trim($customerEmail) . '%'));
    }
    $sm_email = $this->request->getParam('sm_email');
    if ($customerType == '3') {
        $collection->addAttributeToFilter('order.sm_email', array('like' => '%' . trim($customerEmail) . '%'));
    }
    $em_email = $this->request->getParam('em_email');
    if ($customerType == '4') {
        $collection->addAttributeToFilter('order.em_email', array('like' => '%' . trim($customerEmail) . '%'));
    }
    $em_email = $this->request->getParam('em_email');
    if ($customerType == '5') {
        $collection->addAttributeToFilter('order.em_email', array('like' => '%' . trim($customerEmail) . '%'));
    }
    $data = $collection->getData();
    if (count($data) > 0) {
        return $data[0]['total_qty'];
    } else {
        return "0";
    }
}

public function getDue($sku)
{
    $date = date('Y-m-d');
    $customerType = $this->_customerSession->getCustomerType();
    $customerEmail = $this->_customerSession->getCustomer()->getEmail();

    $collection = $this->getItemCollection();
    $collection->addAttributeToSelect('sku');
    $collection->addAttributeToFilter('sku', $sku);
    $collection->addAttributeToFilter('rma_return_status', 'no');
    $collection->getSelect()->join(
        array('order' => $collection->getTable('sales_order')),
        'main_table.order_id = order.entity_id 
        and order.status = "shipping" 
        and order.due_date <= "' . $date . '" 
        and return_status in ("no", "partial")',
        array('entity_id')
    )
        ->columns(array('due' => new \Zend_Db_Expr('ROUND(SUM(main_table.qty_ordered))')))
        ->group('sku');
    $email = $this->request->getParam('email');
    if ($customerType == '1') {
        $collection->addAttributeToFilter('order.customer_email', array('like' => '%' . trim($customerEmail) . '%'));
    }
    $sm_email = $this->request->getParam('sm_email');
    if ($customerType == '3') {
        $collection->addAttributeToFilter('order.sm_email', array('like' => '%' . trim($customerEmail) . '%'));
    }
    $em_email = $this->request->getParam('em_email');
    if ($customerType == '4') {
        $collection->addAttributeToFilter('order.em_email', array('like' => '%' . trim($customerEmail) . '%'));
    }
    $em_email = $this->request->getParam('em_email');
    if ($customerType == '5') {
        $collection->addAttributeToFilter('order.em_email', array('like' => '%' . trim($customerEmail) . '%'));
    }
    $data = $collection->getData();
    if (count($data) > 0) {
        return $data[0]['due'];
    } else {
        return "0";
    }
}

public function getOnDemo($sku)
{
    $customerType = $this->_customerSession->getCustomerType();
    $customerEmail = $this->_customerSession->getCustomer()->getEmail();

    $collection = $this->getItemCollection();
    $collection->addAttributeToSelect('sku');
    $collection->addAttributeToFilter('sku', $sku);
    $collection->addAttributeToFilter('rma_return_status', 'no');
    $collection->getSelect()->join(
        array('order' => $collection->getTable('sales_order')),
        'main_table.order_id = order.entity_id 
        and order.status = "shipping" 
        and return_status in ("no", "partial")',
        array('entity_id')
    )
        ->columns(array('on_demo' => new \Zend_Db_Expr('ROUND(SUM(main_table.qty_ordered))')))
        ->group('sku');
    $email = $this->request->getParam('email');
    if ($customerType == '1') {
        $collection->addAttributeToFilter('order.customer_email', array('like' => '%' . trim($customerEmail) . '%'));
    }
    $sm_email = $this->request->getParam('sm_email');
    if ($customerType == '3') {
        $collection->addAttributeToFilter('order.sm_email', array('like' => '%' . trim($customerEmail) . '%'));
    }
    $em_email = $this->request->getParam('em_email');
    if ($customerType == '4') {
        $collection->addAttributeToFilter('order.em_email', array('like' => '%' . trim($customerEmail) . '%'));
    }
    $em_email = $this->request->getParam('em_email');
    if ($customerType == '5') {
        $collection->addAttributeToFilter('order.em_email', array('like' => '%' . trim($customerEmail) . '%'));
    }
    $data = $collection->getData();
    // print_r($data);die;
    if (count($data) > 0) {
        return $data[0]['on_demo'];
    } else {
        return "0";
    }
}

public function getReturned($sku)
{
    $customerType = $this->_customerSession->getCustomerType();
    $customerEmail = $this->_customerSession->getCustomer()->getEmail();

    $collection = $this->getItemCollection();
    $collection->addAttributeToSelect('sku');
    $collection->addAttributeToFilter('rma_return_status', 'yes');
    $collection->addAttributeToFilter('sku', $sku);
    $collection->getSelect()->join(
        array('order' => $collection->getTable('sales_order')),
        'main_table.order_id = order.entity_id 
        and order.status = "complete" 
        and return_status = "yes"',
        array('entity_id')
    )
        ->columns(array('returned' => new \Zend_Db_Expr('ROUND(SUM(main_table.qty_ordered))')))
        ->group('sku');
    $email = $this->request->getParam('email');
    if ($customerType == '1') {
        $collection->addAttributeToFilter('order.customer_email', array('like' => '%' . trim($customerEmail) . '%'));
    }
    $sm_email = $this->request->getParam('sm_email');
    if ($customerType == '3') {
        $collection->addAttributeToFilter('order.sm_email', array('like' => '%' . trim($customerEmail) . '%'));
    }
    $em_email = $this->request->getParam('em_email');
    if ($customerType == '4') {
        $collection->addAttributeToFilter('order.em_email', array('like' => '%' . trim($customerEmail) . '%'));
    }
    $em_email = $this->request->getParam('em_email');
    if ($customerType == '5') {
        $collection->addAttributeToFilter('order.em_email', array('like' => '%' . trim($customerEmail) . '%'));
    }
    $data = $collection->getData();
    if (count($data) > 0) {
        return $data[0]['returned'];
    } else {
        return "0";
    }
}

public function getItemCollection()
{
    $collection = $this->itemCollectionFactory->create()->getCollection();
    $sku = $this->request->getParam('skus');
    if (isset($sku) && !empty($sku)) {
        $skuArr = explode(",", trim($sku));
        $collection->addAttributeToFilter('sku', array('in', $skuArr));
    }
    // echo "<pre>";print_r($sku);die("erorro");
    return $collection;
}

public function generatePDF($collection)
{
}

public function generateCSV($collection)
{
    // $name = date('m_d_Y_H_i_s');
    // $filepath = 'export/custom' . $name . '.csv';
    // $this->directory->create('export');
    // /* Open file */
    // $stream = $this->directory->openFile($filepath, 'w+');
    // $stream->lock();
    // $columns = $this->getColumnHeader();
    // foreach ($columns as $column) {
    //     $header[] = $column;
    // }

    // $stream->writeCsv($header);
    // foreach ($$collection as $item) {
    //     $orderData = [];
    //     $orderData[] = $item->getName();
    //     $orderData[] = $item->getTotalOrders();
    //     $orderData[] = $item->getTotalQty();
    //     $orderData[] = number_format($this->getOnDemo($item->getSku()));
    //     $orderData[] = number_format($this->getDue($item->getSku()));
    //     $orderData[] = number_format($this->getReturned($item->getSku()));
    //     $stream->writeCsv($orderData);
    // }

    // $content = [];
    // $content['type'] = 'filename'; // must keep filename
    // $content['value'] = $filepath;
    // $content['rm'] = '1'; //remove csv from var folder

    // $csvfilename = 'Order Inventory.csv';
    // return $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
}

/* Header Columns */
public function getColumnHeader()
{
    $headers = ['Item', 'Total Order', 'Total Quantity', 'On Demo', 'Due', 'Returned'];
    return $headers;
}
}

