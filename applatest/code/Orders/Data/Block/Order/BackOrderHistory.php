<?php
namespace Orders\Data\Block\Order;

use \Magento\Framework\App\ObjectManager;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session as CustomerSession;
    
class BackOrderHistory extends \Magento\Sales\Block\Order\History
{
    /**
     * @var string
     */
    
    private $customerSession;
    
    protected $_template = 'Orders_Data::order/backorder.phtml';

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
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

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    protected $_urlInterface;

    protected $customerFactory;
    
    protected $exportHelper;
    
    protected $generatePdf;
    
    protected $salesRep;
    
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
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Sales\Model\Config\Source\Order\Status $orderStatus,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \AscentDigital\Reports\Helper\ExportDueBackReport $exportHelper,
        \AscentDigital\Reports\Helper\Pdf\OrderDueBackPdf $generatePdf,
        \Magento\Framework\App\Request\Http $request,
        \AscentDigital\Reports\Model\SalesReps $salesRep,
        \Magento\Framework\UrlInterface $urlInterface,
        CustomerFactory $customerFactory,
        array $data = []
    ) {
        parent::__construct($context, $orderCollectionFactory, $customerSession, $orderConfig, $data);
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_orderConfig = $orderConfig;
        $this->orderStatus = $orderStatus;
        $this->_storeManager = $storeManager;
        $this->exportHelper = $exportHelper;
        $this->generatePdf = $generatePdf;
        $this->request = $request;
        $this->salesRep = $salesRep;
        $this->_urlInterface = $urlInterface;
        $this->customerFactory = $customerFactory;
        $this->customerSession = $customerSession;
    }

    /**
     * isFirstNet
     * @return boolean
     */
    public function isFirstNet()
    {
        $currentWebsiteId = $this->_storeManager->getStore()->getWebsiteId();
        if ($currentWebsiteId == '3') {
            return true;
        }
        return false;
    }

    public function getAllOrderStatus()
    {
        return $this->orderStatus->toOptionArray();
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
        return $this->_storeManager->getStore()->getUrl("order/index/backorderindex");
    }

    /**
     * Get customer orders
     *
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getOrders()
    {
        

        $orderSearch = $this->request->getParam('search');
        $orderToDate = $this->request->getParam('to_date');
        $email = $this->request->getParam('email');
        $orderExport = $this->request->getParam('export_data');
        $pdfReport = $this->request->getParam('generate_pdf');
        
        $selectedSkus = $this->request->getParam('skus');
        $_selectedSkus = str_replace(",", "|", $selectedSkus);
        /* Get Customer id */
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
        
          $this->orders =   $this->getAllOrders($email,$orderToDate,$customerId,$_selectedSkus);
                
            if(isset($orderExport)){
               $this->exportHelper->exportData($this->orders);
            }
            
            if(isset($pdfReport)){
               $this->generatePdf->generate($this->orders);
            }

        
        
        return $this->orders;
    }
    
    private function getOrderCollectionFactory()
    {
        if ($this->orderCollectionFactory === null) {
            $this->orderCollectionFactory = ObjectManager::getInstance()->get(CollectionFactoryInterface::class);
        }
        return $this->orderCollectionFactory;
    }

    /**
     * Get message for no orders.
     *
     * @return \Magento\Framework\Phrase
     * @since 102.1.0
     */
    public function getEmptyOrdersMessage()
    {
        return __('You have placed no orders or no any selected order status found.');
    }
    
    public function getCustomerType()
    {
        return $this->_customerSession->getCustomerType();
    }
        
     public function getAllOrders($email,$orderToDate,$customerId,$sku){
         
           $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
           $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
           $storeId = $storeManager->getStore()->getStoreId();
         
         
            if($email!='' && $orderToDate!='' && $sku!=''){
                $orders = $this->getOrderCollectionFactory()->create($this->getManagerReps($customerId))->addFieldToSelect(
                    '*'
                )->addAttributeToFilter('due_date', array('from' => '2020-10-12 09:20:25', 'to' => $orderToDate))->addAttributeToFilter('customer_email',['in' => $email])->addFieldToFilter(
                    'return_status',
                    ['nin' => 'yes'])->addAttributeToFilter('status',['in' => 'shipping']);
                    
                $orders->getSelect()->join(array('order_item' => 'sales_order_item'),'main_table.entity_id = order_item.order_id')->group('main_table.entity_id');
                $orders->addFieldToFilter('order_item.sku', array(array('regexp' => $sku)))->addFieldToFilter('main_table.store_id', $storeId)->setOrder(
                        'main_table.customer_ts',
                        'desc'
                );
            }else if($email!='' && $orderToDate=='' && $sku!='') {
                $orderToDate = date('Y-m-d H:i:s');
                $orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
                    '*'
                )->addAttributeToFilter('due_date', array('from' => '2020-10-12 09:20:25', 'to' => $orderToDate))->addAttributeToFilter('customer_email',['in' => $email])->addFieldToFilter(
                    'return_status',
                    ['nin' => 'yes'])->addAttributeToFilter('status',['in' => 'shipping']);
                
                $orders->getSelect()->join(array('order_item' => 'sales_order_item'),'main_table.entity_id = order_item.order_id')->group('main_table.entity_id');
                $orders->addFieldToFilter('order_item.sku', array(array('regexp' => $sku)))->addFieldToFilter('main_table.store_id', $storeId)->setOrder(
                        'main_table.customer_ts',
                        'desc'
                );
            }else if($email!='' && $orderToDate!='' && $sku=='') {
                $orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
                    '*'
                )->addAttributeToFilter('due_date', array('from' => '2020-10-12 09:20:25', 'to' => $orderToDate))->addAttributeToFilter('customer_email',['in' => $email])->addFieldToFilter(
                    'return_status',
                    ['nin' => 'yes']
                )->addAttributeToFilter('status',['in' => 'shipping'])->addFieldToFilter('main_table.store_id', $storeId)
                    ->setOrder(
                        'customer_ts',
                        'desc'
                );
            }else if($email=='' && $orderToDate!='' && $sku!='') {
                $orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
                    '*'
                )->addAttributeToFilter('due_date', array('from' => '2020-10-12 09:20:25', 'to' => $orderToDate))->addFieldToFilter(
                    'return_status',
                    ['nin' => 'yes'])->addAttributeToFilter('status',['in' => 'shipping']);
                    
                $orders->getSelect()->join(array('order_item' => 'sales_order_item'),'main_table.entity_id = order_item.order_id')->group('main_table.entity_id');
                $orders->addFieldToFilter('order_item.sku', array(array('regexp' => $sku)))->addFieldToFilter('main_table.store_id', $storeId)->setOrder(
                        'main_table.customer_ts',
                        'desc'
                );
            }else if($email!='' && $orderToDate=='' && $sku=='') {
                
                $orderToDate = date('Y-m-d H:i:s');
                $orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
                    '*'
                )->addAttributeToFilter('due_date', array('from' => '2020-10-12 09:20:25', 'to' => $orderToDate))->addAttributeToFilter('customer_email',['in' => $email])->addFieldToFilter(
                    'return_status',
                    ['nin' => 'yes']
                )->addAttributeToFilter('status',['in' => 'shipping'])->addFieldToFilter('main_table.store_id', $storeId)
                    ->setOrder(
                        'customer_ts',
                        'desc'
                );
            }
            else if($email=='' && $orderToDate=='' && $sku!='') {
                
                $orderToDate = date('Y-m-d H:i:s');
                $orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
                    '*'
                )->addAttributeToFilter('due_date', array('from' => '2020-10-12 09:20:25', 'to' => $orderToDate))->addFieldToFilter(
                    'return_status',
                    ['nin' => 'yes']
                )->addAttributeToFilter('status',['in' => 'shipping']);
                
                $orders->getSelect()->join(array('order_item' => 'sales_order_item'),'main_table.entity_id = order_item.order_id')->group('main_table.entity_id');
                $orders->addFieldToFilter('order_item.sku', array(array('regexp' => $sku)))->addFieldToFilter('main_table.store_id', $storeId)->setOrder('main_table.customer_ts','desc');
            }else if($email=='' && $orderToDate!='' && $sku=='') {
                
                $orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
                    '*'
                )->addAttributeToFilter('due_date', array('from' => '2020-10-12 09:20:25', 'to' => $orderToDate))->addFieldToFilter(
                    'return_status',
                    ['nin' => 'yes']
                )->addAttributeToFilter('status',['in' => 'shipping'])->addFieldToFilter('main_table.store_id', $storeId)
                    ->setOrder(
                        'customer_ts',
                        'desc'
                );
            }
            
            
            else if($email=='' && $orderToDate=='' && $sku=='') {
                
                $orderToDate = date('Y-m-d H:i:s');
                $orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
                    '*'
                )->addAttributeToFilter('due_date', array('from' => '2020-10-12 09:20:25', 'to' => $orderToDate))->addFieldToFilter(
                    'return_status',
                    ['nin' => 'yes']
                )->addAttributeToFilter('status',['in' => 'shipping'])->addFieldToFilter('main_table.store_id', $storeId)
                    ->setOrder(
                        'customer_ts',
                        'desc'
                );
            }
        return $orders;
        }
    public function getProducts($skus) {
        $_storeId = $this->_storeManager->getStore()->getId();
        $_storeId = 2; //firstnet

        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*')
        ->addFieldToFilter('sku', array('in'=> $skus));
        $collection->addStoreFilter($_storeId);

        $productsData = array();

        foreach($collection as $product) {
            $sku = $product->getSku();
            $pid = $product->getId();
            $pName = $product->getName();
            $productsData[$pid]['sku'] = $sku;
            $productsData[$pid]['name'] = $pName;
            //echo $pName." : ".$sku."<br/>";
        }

         //echo "<pre>";print_r($productsData);die();
         return $productsData;
    }
}
