<?php
// CHM-MA

namespace AscentDigital\Reports\Block\Reports\FirstNet;

use \Magento\Framework\App\ObjectManager;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\Customer\Model\CustomerFactory;

class OrderInventoryReport extends \Magento\Sales\Block\Order\History
{
    /**
     * @var string
     */
    protected $_template = 'AscentDigital_Reports::orderinventoryreport.phtml';

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_orderCollectionFactory;
    
    protected $itemCollectionFactory;

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

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    protected $_urlInterface;

    protected $customerFactory;
    
    protected $exportHelper;
    
    protected $salesRep;
    
    protected $generatePdf;
    
    protected $_productCollectionFactory;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Sales\Model\Config\Source\Order\Status $orderStatus
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Request\Http $request
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\Order\ItemFactory $itemCollectionFactory,

        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Sales\Model\Config\Source\Order\Status $orderStatus,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Request\Http $request,
        \AscentDigital\Reports\Model\SalesReps $salesRep,
        \AscentDigital\Reports\Helper\ExportOrderInventory $exportHelper,
        \AscentDigital\Reports\Helper\Pdf\OrderInventoryPdf $generatePdf,
        \Magento\Framework\UrlInterface $urlInterface,
        CustomerFactory $customerFactory,
        array $data = []
    ) {
        parent::__construct($context, $orderCollectionFactory, $customerSession, $orderConfig, $data);
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->itemCollectionFactory = $itemCollectionFactory;

        $this->_customerSession = $customerSession;
        $this->_orderConfig = $orderConfig;
        $this->orderStatus = $orderStatus;
        $this->_storeManager = $storeManager;
        $this->request = $request;
        $this->exportHelper = $exportHelper;
        $this->generatePdf = $generatePdf;
        $this->salesRep = $salesRep;
        $this->_urlInterface = $urlInterface;
        $this->customerFactory = $customerFactory;
    }

    public function getCurrentURL()
    {
        return $this->_urlInterface->getCurrentUrl();
    }

    public function getOrderStatusURL($statusVal)
    {
        $link = $this->getHistoryPageURL();
        $paramsData = $this->request->getParams();
        if (count($paramsData)) {
            $link .= "?";
            /* Page Number */
            if (isset($paramsData['p'])) {
                $link .= "p=" . $paramsData['p'] . "&";
            }
            /* Page Limit Number */
            if (isset($paramsData['limit'])) {
                $link .= "limit=" . $paramsData['limit'] . "&";
            }
            $link .= "status=" . $statusVal . "&";
        } else {
            $link .= "?";
            $link .= "status=" . $statusVal . "&";
        }
        $link = rtrim(rtrim($link, "&"), "?");
        return $link;
    }

    public function getHistoryPageURL()
    {
        return $this->_storeManager->getStore()->getUrl("mobilecg/salesmanager/orderinventoryreport");
    }

//     /**
//      * Get customer orders
//      *
//      * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
//      */
    public function getOrders()
    {
       // $sku = $this->request->getParam('skus');
        $orderSearch = $this->request->getParam('search');
        $orderExport = $this->request->getParam('export_data');
        $pdfReport = $this->request->getParam('generate_pdf');
        $email = $this->request->getParam('email');


        $selectedSkus = $this->request->getParam('skus');
        $_selectedSkus = str_replace(",", "|", $selectedSkus);
        
        /* Get Customer id */
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
        if (!$this->orders) {
            
            $this->orders = $this->getAllOrders($_selectedSkus,$customerId,$email);
            // echo"<pre>";print_r($this->orders->getData());die("error");
       }

        if(isset($orderExport)){
            $this->exportHelper->exportData($this->orders);
        }
        
        if(isset($pdfReport)){
            $this->generatePdf->generate($this->orders);
        }
        
        return $this->orders;
    }

    

    public function getEmptyOrdersMessage()
    {
        return __('You have placed no orders or no any selected order status found.');
    }


    
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        
        if ($this->getProducts()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'orderaproval.history.pager'
            )->setAvailableLimit([10 => 10, 15 => 15, 20 => 20])
                ->setShowPerPage(true)->setCollection(
                    $this->getProducts()
                );
            $this->setChild('pager', $pager);
            $this->getProducts()->load();
        }
        return $this;
    }

    /**
     * Get Pager child block output
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    
    
    

    public function getCustomerType(){
        return $this->_customerSession->getCustomerType();
    }

// getting product collection with filters
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
    return $collection;
}
// getting products for spacific customer's
public function getProducts()
{
    
    $customerType = $this->_customerSession->getCustomerType();
    $customerEmail = $this->_customerSession->getCustomer()->getEmail();
    $collection = $this->getProductCollection();   

    
   
   
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
   
    return $collection;
}
// getting products on the basis of sku
public function getProductSkus($skus)
{
    $_storeId = $this->_storeManager->getStore()->getId();
    $_storeId = 2; //firstnet

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
    }

    return $productsData;
}
// getting on due orders
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
// getting on Demo orders

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
// getting Return orders

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
// item collection factory
public function getItemCollection()
{
    $collection = $this->itemCollectionFactory->create()->getCollection();
    $sku = $this->request->getParam('skus');
    if (isset($sku) && !empty($sku)) {
        $skuArr = explode(",", trim($sku));
        $collection->addAttributeToFilter('sku', array('in', $skuArr));
    }
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

