<?php

namespace AscentDigital\Reports\Block\Adminhtml\Reports;
    
use Magento\Backend\Block\Template;
use \Magento\Framework\App\ObjectManager;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\Customer\Model\CustomerFactory;

class TotalSalesRepOrdersReport extends \Magento\Sales\Block\Order\History
{
    /**
     * @var string
     */
    protected $_template = 'AscentDigital_Reports::total_sales_orders_report.phtml';

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
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Sales\Model\Config\Source\Order\Status $orderStatus,
        \AscentDigital\Reports\Helper\ExportOrderDetailReport $exportHelper,
        \AscentDigital\Reports\Helper\Pdf\OrderDetailPdf $generatePdf,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        
        \Magento\Framework\App\Request\Http $request,
        \AscentDigital\Reports\Model\SalesReps $salesRep,
        \Magento\Framework\UrlInterface $urlInterface,
        CustomerFactory $customerFactory,
        
        array $data = []
    ) {
        parent::__construct($context, $orderCollectionFactory, $customerSession, $orderConfig, $data);
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->_orderConfig = $orderConfig;
        $this->exportHelper = $exportHelper;
        $this->generatePdf = $generatePdf;
        $this->orderStatus = $orderStatus;
        $this->salesRep = $salesRep;
        $this->_storeManager = $storeManager;
        
        $this->request = $request;
        $this->_urlInterface = $urlInterface;
        $this->customerFactory = $customerFactory;
        
    }

    public function getAllOrderStatus()
    {
       return [
            [
                'value' => 0,
                'label' => __('All'),
            ],
            [
                'value' => 'Shipping',
                'label' => __('Shipping'),
            ],
            [
                'value' => 'Processing',
                'label' => __('Processing'),
            ],
            [
                'value' => 'Complete',
                'label' => __('Complete'),
            ],
        ];
    }

    public function getCurrentURL()
    {
        return $this->_urlInterface->getCurrentUrl();
    }

    public function getHistoryPageURL()
    {
        return $this->getUrl('adminreports/grid/totalsalesrepordersreport', ['_current' => true]);
    }

    /**
     * Get customer orders
     *
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getOrders()
    {

        /* Get order status */
        $orderStatus = $this->request->getParam('status');
        $orderFrom = $this->request->getParam('from');
        $orderTo = $this->request->getParam('to');
        $orderSearch = $this->request->getParam('search');
        $Orderfrom = date('Y-m-d H:i:s', strtotime($orderFrom));
        $orderTo = date('Y-m-d H:i:s', strtotime($orderTo . ' +1 day'));
        $email = $this->request->getParam('email');
        
        $orderExport = $this->request->getParam('export_data');
        
        $selectedSkus = $this->request->getParam('skus');
        $_selectedSkus = str_replace(",", "|", $selectedSkus);

        $salesmanageremail = $this->request->getParam('sm_email');
        $teritorymanageremail = $this->request->getParam('tm_email');
        $executivemanageremail = $this->request->getParam('em_email');
        
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $baseUrl = $storeManager->getStore()->getBaseUrl();
       
        $orderStatus = ['shipping', 'processing','complete'];
        
        if (!$this->orders) {
              $this->orders = $this->statusBaseFilters($orderStatus,$orderFrom,$this->request->getParam('to'),$email,$_selectedSkus,$salesmanageremail,$teritorymanageremail,$executivemanageremail);
         }
        
        if(isset($orderExport)){
            $this->exportHelper->exportData($this->orders);
        }

        return $this->orders;
    }




    /**
     * Provide order collection factory
     *
     * @return CollectionFactoryInterface
     * @deprecated 100.1.1
     */
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
    
    
    
    public function getProductSkus($skus)
    {

        $_storeId = $this->_storeManager->getStore()->getId();
        $_storeId = 2; //firstnet
         $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productCollectionFactory = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        // $collection = $this->getItemCollection();
        $collection = $productCollectionFactory->create();
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

        // echo "<pre>";print_r($productsData);die();
        return $productsData;
    }
    
    public function getTotalQty($customerEmail){
        $qty=0.00;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $storeId = $storeManager->getStore()->getStoreId();
        
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
        
        $this->orders  = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
            '*'
        )->addFieldToFilter(
            'status',
            array("in",'Shipping','Processing','Complete')
        )->columns(array('returned' => new \Zend_Db_Expr('ROUND(SUM(main_table.qty_ordered))')))
        ->addFieldToFilter('main_table.store_id', $storeId)->addFieldToFilter(
            'customer_email',
            ['in' => $customerEmail]
        )->group('sku');
        
        foreach($this->orders as $order){
            $qty= $order['returned'];
        }
        return $qty;
    }
    
    public function getOrderCount($customerEmail){
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $storeId = $storeManager->getStore()->getStoreId();
        
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
        
        $this->orders  = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
            '*'
        )->addFieldToFilter(
            'status',
            array("in",'Shipping','Processing','Complete')
        )->addFieldToFilter('main_table.store_id', $storeId)->addFieldToFilter(
            'customer_email',
            ['in' => $customerEmail]
        );
        $count = count($this->orders);
        return $count;
    }
    
    public function getLateOrderCount($customerEmail){
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
               $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
               $storeId = $storeManager->getStore()->getStoreId();
        
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
        
        $orderToDate = date('Y-m-d H:i:s');
        $orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
            '*'
        )->addFieldToFilter('main_table.store_id', $storeId)->addAttributeToFilter('due_date', array('from' => '2020-10-12 09:20:25', 'to' => $orderToDate))->addFieldToFilter(
            'return_status',
            ['nin' => 'yes']
        )->addFieldToFilter(
            'customer_email',
            ['in' => $customerEmail]
        )->addAttributeToFilter('status',['in' => 'shipping'])
            ->setOrder(
                'customer_ts',
                'desc'
        );
        
        
        $count = count($orders);
        return $count;
    }
    
    public function getCompletedOrderCount($customerEmail){
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
               $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
               $storeId = $storeManager->getStore()->getStoreId();
        
        
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
        
        $this->orders  = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
            '*'
        )->addFieldToFilter('main_table.store_id', $storeId)->addFieldToFilter(
            'status',
            array("in",'Complete')
        )->addFieldToFilter(
            'return_status',
            ['in' => 'yes']
        )->addFieldToFilter(
            'customer_email',
            ['in' => $customerEmail]
        );
       
        $count = count($this->orders);
        return $count;
    }
    
    public function getDemoOrderCount($customerEmail){
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
               $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
               $storeId = $storeManager->getStore()->getStoreId();
        
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
        
        $this->orders = $this->_orderCollectionFactory->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
            '*'
        )->addFieldToFilter('main_table.store_id', $storeId)->addFieldToFilter(
            'status',
            ['in' => 'shipping']
        )->addFieldToFilter(
            'return_status',
            ['neq' => 'yes']
        )->addFieldToFilter(
            'customer_email',
            ['in' => $customerEmail]
        )->setOrder(
                'customer_ts',
                'desc');
        $count = count($this->orders);
        return $count;
        
    }
    
    
    public function statusBaseFilters($orderStatus,$orderFrom,$orderTo,$email,$skus,$salesmanageremail,$teritorymanageremail,$executivemanageremail){
        
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
    $storeId = $storeManager->getStore()->getStoreId();
        
        if($orderFrom!='' && $orderTo!='' && $email!='' && $skus!='' && $salesmanageremail=='' && $teritorymanageremail=='' && $executivemanageremail==''){
           $this->orders = $this->getOrderCollectionFactory()->create()->addFieldToSelect(
               '*'
           )->addAttributeToFilter('customer_ts', array('from' => $orderFrom, 'to' => $orderTo))
               ->addFieldToFilter(
                   'status',
                   ['in' => $orderStatus]
               )->addAttributeToFilter('customer_email',['in' => $email]);
            $this->orders->getSelect()->join(array('order_item' => 'sales_order_item'),'main_table.entity_id = order_item.order_id');
            $this->orders->addFieldToFilter('order_item.sku', array(array('regexp' => $skus)))->addFieldToFilter('main_table.store_id', $storeId)->setOrder('main_table.customer_ts','desc');
            
           
        }else if($orderFrom=='' && $orderTo=='' && $email!='' && $skus=='' && $salesmanageremail=='' && $teritorymanageremail=='' && $executivemanageremail==''){
            
            
           $this->orders = $this->getOrderCollectionFactory()->create()->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                    'status',
                    ['in' => $orderStatus]
                )->addFieldToFilter('main_table.store_id', $storeId)->addAttributeToFilter('customer_email',['in' => $email])
                ->setOrder(
                    'customer_ts',
                    'desc'
                );
        }else if($orderFrom!='' && $orderTo!='' && $email=='' && $skus=='' && $salesmanageremail=='' && $teritorymanageremail=='' && $executivemanageremail==''){
            
            $this->orders = $this->getOrderCollectionFactory()->create()->addFieldToSelect(
                '*'
            )->addAttributeToFilter('customer_ts', array('from' => $orderFrom, 'to' => $orderTo))
                ->addFieldToFilter(
                    'status',
                    ['in' => $orderStatus]
                )->addFieldToFilter('main_table.store_id', $storeId)
                ->setOrder(
                    'customer_ts',
                    'desc'
                );
            
        }else if($orderFrom!='' && $orderTo!='' && $email!='' && $skus=='' && $salesmanageremail=='' && $teritorymanageremail=='' && $executivemanageremail==''){
           $this->orders = $this->getOrderCollectionFactory()->create()->addFieldToSelect(
               '*'
           )->addAttributeToFilter('customer_ts', array('from' => $orderFrom, 'to' => $orderTo))
               ->addFieldToFilter(
                   'status',
                   ['in' => $orderStatus]
               )->addFieldToFilter('main_table.store_id', $storeId)->addAttributeToFilter('customer_email',['in' => $email])->setOrder('customer_ts','desc');
            
           
        }
        
        else if($orderFrom!='' && $orderTo!='' && $email=='' && $skus!='' && $salesmanageremail=='' && $teritorymanageremail=='' && $executivemanageremail==''){
            
            $this->orders = $this->getOrderCollectionFactory()->create()->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                    'status',
                    ['in' => $orderStatus]
             );
            
             $this->orders->getSelect()->join(array('order_item' => 'sales_order_item'),'main_table.entity_id = order_item.order_id');
             $this->orders->addFieldToFilter('order_item.sku', array(array('regexp' => $skus)))->addFieldToFilter('main_table.store_id', $storeId)->setOrder('main_table.customer_ts','desc');
        } else if($orderFrom=='' && $orderTo=='' && $email!='' && $skus!='' && $salesmanageremail=='' && $teritorymanageremail=='' && $executivemanageremail==''){
            
            $this->orders = $this->getOrderCollectionFactory()->create()->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                    'status',
                    ['in' => $orderStatus]
            )->addAttributeToFilter('customer_email',['in' => $email]);
            $this->orders->getSelect()->join(array('order_item' => 'sales_order_item'),'main_table.entity_id = order_item.order_id');
            $this->orders->addFieldToFilter('order_item.sku', array(array('regexp' => $skus)))->addFieldToFilter('main_table.store_id', $storeId)->setOrder('main_table.customer_ts','desc');
        }
        
        else if($orderFrom=='' && $orderTo=='' && $email=='' && $skus!='' && $salesmanageremail=='' && $teritorymanageremail=='' && $executivemanageremail==''){
            
            $this->orders = $this->getOrderCollectionFactory()->create()->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                    'status',
                    ['in' => $orderStatus]
            );
            $this->orders->getSelect()->join(array('order_item' => 'sales_order_item'),'main_table.entity_id = order_item.order_id');
            $this->orders->addFieldToFilter('order_item.sku', array(array('regexp' => $skus)))->addFieldToFilter('main_table.store_id', $storeId)->setOrder('main_table.customer_ts','desc');
        }else if($orderFrom=='' && $orderTo=='' && $email=='' && $skus=='' && $salesmanageremail=='' && $teritorymanageremail=='' && $executivemanageremail==''){
           
            $this->orders = $this->getOrderCollectionFactory()->create()->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                    'status',
                    ['in' => $orderStatus]
                )->addFieldToFilter('main_table.store_id', $storeId)
                ->setOrder(
                    'main_table.customer_ts',
                    'desc'
                );
        }else if($salesmanageremail!='' && $orderFrom=='' && $orderTo=='' && $email=='' && $skus==''  && $teritorymanageremail=='' && $executivemanageremail==''){
            
            $this->orders = $this->getOrderCollectionFactory()->create()->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                    'status',
                    ['in' => $orderStatus]
                )->addAttributeToFilter('main_table.sm_email',['in' => $salesmanageremail])->addFieldToFilter('main_table.store_id', $storeId)
                ->setOrder(
                    'main_table.customer_ts',
                    'desc'
                );
        }else if($teritorymanageremail!='' && $orderFrom=='' && $orderTo=='' && $email=='' && $skus=='' && $salesmanageremail=='' && $executivemanageremail==''){
            
            $this->orders = $this->getOrderCollectionFactory()->create()->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                    'status',
                    ['in' => $orderStatus]
                )->addAttributeToFilter('main_table.tm_email',['in' => $teritorymanageremail])->addFieldToFilter('main_table.store_id', $storeId)
                ->setOrder(
                    'main_table.customer_ts',
                    'desc'
                );
            
        }else if($executivemanageremail!='' && $orderFrom=='' && $orderTo=='' && $email=='' && $skus=='' && $salesmanageremail=='' && $teritorymanageremail==''){
            
            $this->orders = $this->getOrderCollectionFactory()->create()->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                    'status',
                    ['in' => $orderStatus]
                )->addAttributeToFilter('main_table.em_email',['in' => $executivemanageremail] )->addFieldToFilter('main_table.store_id', $storeId)
                ->setOrder(
                    'main_table.customer_ts',
                    'desc'
                );
        }
        
        return  $this->orders;
    }
}

