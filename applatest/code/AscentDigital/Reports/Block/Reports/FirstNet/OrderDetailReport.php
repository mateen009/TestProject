<?php

namespace AscentDigital\Reports\Block\Reports\FirstNet;

use \Magento\Framework\App\ObjectManager;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\Customer\Model\CustomerFactory;

class OrderDetailReport extends \Magento\Sales\Block\Order\History
{
    /**
     * @var string
     */
    protected $_template = 'AscentDigital_Reports::orderdetailreport.phtml';

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
        $this->_productCollectionFactory = $productCollectionFactory;
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
                'label' => __('On Demo'),
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
        return $this->_storeManager->getStore()->getUrl("mobilecg/salesmanager/orderdetailreport");
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
        $email = $this->request->getParam('email');
        $orderExport = $this->request->getParam('export_data');
        $pdfReport = $this->request->getParam('generate_pdf');
        
        $selectedSkus = $this->request->getParam('skus');
        $_selectedSkus = str_replace(",", "|", $selectedSkus);
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $baseUrl = $storeManager->getStore()->getBaseUrl();
        
        
        if($orderTo!=''){
                   $orderTo = date('Y-m-d H:i:s', strtotime($orderTo . ' +1 day'));
               }else{
                   $orderTo = $orderTo;
               }
        
        if (strpos($this->request->getParam('status'), $baseUrl.'mobilecg/salesmanager/orderdetailreport/') !== false) {
            $orderStatus = trim(str_replace($baseUrl."mobilecg/salesmanager/orderdetailreport/", "", $this->request->getParam('status')));
        }

        if (!$orderStatus || empty($orderStatus)) {
            $orderStatus = ['shipping', 'processing','complete'];
        } else {
            $orderStatus = [];
            $orderStatus[] = $this->request->getParam('status');
        }

        if (strpos($this->request->getParam('status'), $baseUrl.'mobilecg/salesmanager/orderdetailreport/?status=') !== false) {
            $orderStatus = trim(str_replace($baseUrl."mobilecg/salesmanager/orderdetailreport/?status=", "", $this->request->getParam('status')));
        }

        /* Get Customer id */
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
        
        if (!$this->orders) {
            
            $this->orders = $this->statusBaseFilters($orderStatus,$orderFrom,$orderTo,$email,$customerId,$_selectedSkus);
            
            if(isset($orderExport)){
                $this->exportHelper->exportData($this->orders);
            }
            
            if(isset($pdfReport)){
                $this->generatePdf->generate($this->orders);
            }
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
    
    
    
    public function statusBaseFilters($orderStatus,$orderFrom,$orderTo,$email,$customerId,$skus){
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $storeId = $storeManager->getStore()->getStoreId();
        
        
        if($orderFrom!='' && $orderTo!='' && $email!='' && $skus!=''){
           $this->orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
               '*'
           )->addAttributeToFilter('customer_ts', array('from' => $orderFrom, 'to' => $orderTo))
               ->addFieldToFilter(
                   'status',
                   ['in' => $orderStatus]
               )->addAttributeToFilter('customer_email',['in' => $email]);
            $this->orders->getSelect()->join(array('order_item' => 'sales_order_item'),'main_table.entity_id = order_item.order_id');
            $this->orders->getSelect()->group('main_table.entity_id');
            $this->orders->addFieldToFilter('order_item.sku', array(array('regexp' => $skus)))->addFieldToFilter('main_table.store_id', $storeId)->setOrder('main_table.customer_ts','desc');
            $this->orders->getSelect()->group('main_table.entity_id');
           
        }else if($orderFrom=='' && $orderTo=='' && $email!='' && $skus==''){
            
            
           $this->orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                    'status',
                    ['in' => $orderStatus]
                )->addAttributeToFilter('customer_email',['in' => $email])->addFieldToFilter('main_table.store_id', $storeId)
                ->setOrder(
                    'customer_ts',
                    'desc'
                );
            $this->orders->getSelect()->group('main_table.entity_id');
        }else if($orderFrom!='' && $orderTo!='' && $email=='' && $skus==''){
            
            $this->orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
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
            $this->orders->getSelect()->group('main_table.entity_id');
        }else if($orderFrom!='' && $orderTo!='' && $email!='' && $skus==''){
           $this->orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
               '*'
           )->addAttributeToFilter('customer_ts', array('from' => $orderFrom, 'to' => $orderTo))
               ->addFieldToFilter(
                   'status',
                   ['in' => $orderStatus]
               )->addFieldToFilter('main_table.store_id', $storeId)->addAttributeToFilter('customer_email',['in' => $email])->setOrder('customer_ts','desc');
            
           $this->orders->getSelect()->group('main_table.entity_id');
        }
        
        else if($orderFrom!='' && $orderTo!='' && $email=='' && $skus!=''){
            
            $this->orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                    'status',
                    ['in' => $orderStatus]
             );
            
             $this->orders->getSelect()->join(array('order_item' => 'sales_order_item'),'main_table.entity_id = order_item.order_id');
             $this->orders->getSelect()->group('main_table.entity_id');
             $this->orders->addFieldToFilter('order_item.sku', array(array('regexp' => $skus)))->addFieldToFilter('main_table.store_id', $storeId)->setOrder('main_table.customer_ts','desc');
            $this->orders->getSelect()->group('main_table.entity_id');
        } else if($orderFrom=='' && $orderTo=='' && $email!='' && $skus!=''){
            
            $this->orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                    'status',
                    ['in' => $orderStatus]
            )->addAttributeToFilter('customer_email',['in' => $email]);
            $this->orders->getSelect()->join(array('order_item' => 'sales_order_item'),'main_table.entity_id = order_item.order_id');
            $this->orders->getSelect()->group('main_table.entity_id');
            $this->orders->addFieldToFilter('order_item.sku', array(array('regexp' => $skus)))->addFieldToFilter('main_table.store_id', $storeId)->setOrder('main_table.customer_ts','desc');
            $this->orders->getSelect()->group('main_table.entity_id');
        }
        
        else if($orderFrom=='' && $orderTo=='' && $email=='' && $skus!=''){
            
            $this->orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                    'status',
                    ['in' => $orderStatus]
            );
            $this->orders->getSelect()->join(array('order_item' => 'sales_order_item'),'main_table.entity_id = order_item.order_id');
            $this->orders->getSelect()->group('main_table.entity_id');
            $this->orders->addFieldToFilter('order_item.sku', array(array('regexp' => $skus)))->addFieldToFilter('main_table.store_id', $storeId)->setOrder('main_table.customer_ts','desc');
            $this->orders->getSelect()->group('main_table.entity_id');
        }else if($orderFrom=='' && $orderTo=='' && $email=='' && $skus==''){
           
            $this->orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                    'status',
                    ['in' => $orderStatus]
                )->addFieldToFilter('main_table.store_id', $storeId)
                ->setOrder(
                    'customer_ts',
                    'desc'
                );
            $this->orders->getSelect()->group('main_table.entity_id');
        }
        
        return  $this->orders;
    }
    public function getCustomerType(){
        return $this->_customerSession->getCustomerType();
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
