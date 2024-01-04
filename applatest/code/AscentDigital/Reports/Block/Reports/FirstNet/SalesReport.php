<?php

namespace AscentDigital\Reports\Block\Reports\FirstNet;

use \Magento\Framework\App\ObjectManager;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\Customer\Model\CustomerFactory;

class SalesReport extends \Magento\Sales\Block\Order\History
{
    /**
     * @var string
     */
    protected $_template = 'AscentDigital_Reports::salesreport.phtml';

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
        \Magento\Framework\App\Request\Http $request,
        \AscentDigital\Reports\Helper\ExportSalesRepOrders $exportHelper,
        \AscentDigital\Reports\Helper\Pdf\OrderSalesPdf $generatePdf,
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
        $this->orderStatus = $orderStatus;
        $this->_storeManager = $storeManager;
        $this->request = $request;
        $this->exportHelper = $exportHelper;
        $this->generatePdf = $generatePdf;
        $this->salesRep = $salesRep;
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
        return $this->_storeManager->getStore()->getUrl("mobilecg/salesmanager/salesreport");
    }

    /**
     * Get customer orders
     *
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getOrders()
    {

        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
            $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest(
                
            )->getParam('limit') : 10;
            
            
        
        $orderStatus = $this->request->getParam('status');
        $email = $this->request->getParam('email');
        $orderFrom = $this->request->getParam('from');
        $orderTo = $this->request->getParam('to');
        $orderSearch = $this->request->getParam('search');
        $orderExport = $this->request->getParam('export_data');
        $pdfReport = $this->request->getParam('generate_pdf');
        
        $selectedSkus = $this->request->getParam('skus');
        $_selectedSkus = str_replace(",", "|", $selectedSkus);
        
             if($orderTo!=''){
                  $orderTo = date('Y-m-d H:i:s', strtotime($orderTo . ' +1 day'));
              }else{
                  $orderTo = $orderTo;
              }
        
        
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $baseUrl = $storeManager->getStore()->getBaseUrl();
        $storeId = $storeManager->getStore()->getStoreId();

        if (strpos($this->request->getParam('status'), $baseUrl . 'mobilecg/salesmanager/salesreport/') !== false) {
            $orderStatus = trim(str_replace($baseUrl . "mobilecg/salesmanager/salesreport/", "", $this->request->getParam('status')));
        }

        if (!$orderStatus || empty($orderStatus)) {
            $orderStatus = ['Shipping','Processing','Complete'];
        } else {
            $orderStatus = [];
            $orderStatus[] = $this->request->getParam('status');
        }

        if (strpos($this->request->getParam('status'), $baseUrl . 'mobilecg/salesmanager/salesreport/?status=') !== false) {
            $orderStatus = trim(str_replace($baseUrl . "mobilecg/salesmanager/salesreport/?status=", "", $this->request->getParam('status')));
        }

        
        /* Get Customer id */
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }

        $this->orders = $this->statusBaseFilters($orderStatus,$orderFrom,$orderTo,$email,$customerId,$_selectedSkus,$storeId);

        if(isset($orderExport)){
           $this->exportHelper->exportData($this->orders);
        }

        if(isset($pdfReport)){
           $this->generatePdf->generate($this->orders);
        }
        
        $this->orders->setPageSize($pageSize);
        $this->orders->setCurPage($page);

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

    public function getApprovalStatus1($orderId)
    {
        $status = "";
        $this->orders = $this->getOrderCollectionFactory()->create()->addFieldToSelect(
            '*'
        )->addAttributeToFilter('entity_id', $orderId);

        foreach ($this->orders as $data) {
            $status = $data['approval_1_status'];
        }


        return $status;
    }

    public function getApprovalStatus2($orderId)
    {
        $status = "";
        $this->orders = $this->getOrderCollectionFactory()->create()->addFieldToSelect(
            '*'
        )->addAttributeToFilter('entity_id', $orderId);

        foreach ($this->orders as $data) {
            $status = $data['approval_2_status'];
        }


        return $status;
    }
    
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getOrders()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'orderaproval.history.pager'
            )->setAvailableLimit([10 => 10, 15 => 15, 20 => 20])
                ->setShowPerPage(true)->setCollection(
                    $this->getOrders()
                );
            $this->setChild('pager', $pager);
            $this->getOrders()->load();
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
       
 public function statusBaseFilters($orderStatus,$orderFrom,$orderTo,$email,$customerId,$skus,$storeId){
     
           if($orderFrom!='' && $orderTo!='' && $email!='' && $skus!=''){
               
              $this->orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
                  '*'
              )->addAttributeToFilter('customer_ts', array('from' => $orderFrom, 'to' => $orderTo))
                  ->addFieldToFilter(
                      'status',
                      ['in' => $orderStatus]
                  )->addFieldToFilter('main_table.store_id', $storeId)->addAttributeToFilter('customer_email',['in' => $email]);
               
               $this->orders->getSelect()->join(array('order_item' => 'sales_order_item'),'main_table.entity_id = order_item.order_id');
               $this->orders->getSelect()->group('main_table.entity_id');
               $this->orders->addFieldToFilter('order_item.sku', array(array('regexp' => $skus)))->setOrder('main_table.customer_ts','desc');
               $this->orders->getSelect()->group('main_table.entity_id');
           }else if($orderFrom!='' && $orderTo!='' && $email=='' && $skus!=''){
               
              $this->orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
                  '*'
              )->addFieldToFilter('main_table.store_id', $storeId)->addAttributeToFilter('customer_ts', array('from' => $orderFrom, 'to' => $orderTo))
                  ->addFieldToFilter(
                      'status',
                      ['in' => $orderStatus]
              );
               
               $this->orders->getSelect()->join(array('order_item' => 'sales_order_item'),'main_table.entity_id = order_item.order_id');
               $this->orders->getSelect()->group('main_table.entity_id');
               $this->orders->addFieldToFilter('order_item.sku', array(array('regexp' => $skus)))->setOrder('main_table.customer_ts','desc');
               $this->orders->getSelect()->group('main_table.entity_id');
           }else if($orderFrom!='' && $orderTo!='' && $email!='' && $skus==''){
               
              $this->orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
                  '*'
              )->addFieldToFilter('main_table.store_id', $storeId)->addAttributeToFilter('customer_ts', array('from' => $orderFrom, 'to' => $orderTo))
                  ->addFieldToFilter(
                      'status',
                      ['in' => $orderStatus]
                  )->addAttributeToFilter('customer_email',['in' => $email])->setOrder('customer_ts','desc');
               $this->orders->getSelect()->group('main_table.entity_id');
           }else if($orderFrom=='' && $orderTo=='' && $email!='' && $skus!=''){
              
              $this->orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
                  '*'
              )->addFieldToFilter(
                      'status',
                      ['in' => $orderStatus]
              )->addFieldToFilter('main_table.store_id', $storeId)->addAttributeToFilter('customer_email',['in' => $email]);
               
               $this->orders->getSelect()->join(array('order_item' => 'sales_order_item'),'main_table.entity_id = order_item.order_id');
               $this->orders->getSelect()->group('main_table.entity_id');
               $this->orders->addFieldToFilter('order_item.sku', array(array('regexp' => $skus)))->setOrder('main_table.customer_ts','desc');
               $this->orders->getSelect()->group('main_table.entity_id');
           }else if($orderFrom=='' && $orderTo=='' && $email!='' && $skus==''){
               
               
              $this->orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
                  '*'
              )->addFieldToFilter(
                      'status',
                      ['in' => $orderStatus]
                  )->addFieldToFilter('main_table.store_id', $storeId)->addAttributeToFilter('customer_email',['in' => $email])->setOrder('customer_ts','desc');
               $this->orders->getSelect()->group('main_table.entity_id');
           }else if($orderFrom=='' && $orderTo=='' && $email=='' && $skus!=''){
               
              $this->orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
                  '*'
              )->addFieldToFilter(
                      'status',
                      ['in' => $orderStatus]
              )->addFieldToFilter('main_table.store_id', $storeId);
               
               $this->orders->getSelect()->join(array('order_item' => 'sales_order_item'),'main_table.entity_id = order_item.order_id');
               $this->orders->getSelect()->group('main_table.entity_id');
               $this->orders->addFieldToFilter('order_item.sku', array(array('regexp' => $skus)))->setOrder('main_table.customer_ts','desc');
               $this->orders->getSelect()->group('main_table.entity_id');
           }else if($orderFrom!='' && $orderTo!='' && $email=='' && $skus==''){
               
               $this->orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
                   '*'
               )->addFieldToFilter('main_table.store_id', $storeId)->addAttributeToFilter('customer_ts', array('from' => $orderFrom, 'to' => $orderTo))->addFieldToFilter('status',['in' => $orderStatus]
               )->setOrder(
                       'customer_ts',
                       'desc'
               );
               $this->orders->getSelect()->group('main_table.entity_id');
           }else if($orderFrom=='' && $orderTo=='' && $email=='' && $skus==''){
               $this->orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
                   '*'
               )->addFieldToFilter(
                   'status',
                   ['in' => $orderStatus]
               )->addFieldToFilter('main_table.store_id', $storeId)->setOrder(
                   'customer_ts',
                   'desc'
               );
               
               $this->orders->getSelect()->group('main_table.entity_id');
           }
        //    echo "<pre>";print_r($this->orders->getData());die;
           return  $this->orders;
       }
    public function getCustomerType(){
        return $this->_customerSession->getCustomerType();
    }
    
    public function getProducts($skus) {
        $_storeId = $this->_storeManager->getStore()->getId();
        $_storeId = 2; //firstnet

        $collection = $this->_productCollectionFactory->create();
        $collection->distinct(true)
        ->addAttributeToSelect('*')
        ->addFieldToFilter('sku', array('in'=> $skus))
        ;

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

        //  echo "<pre>";print_r($productsData);die();
         return $productsData;
    }
    
    public function isDueOrder($orderId){
        $isDeu='';
        
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
        
        $orderToDate = date('Y-m-d H:i:s');
        $orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
            '*'
        )->addAttributeToFilter('due_date', array('from' => '2020-10-12 09:20:25', 'to' => $orderToDate))->addFieldToFilter(
            'return_status',
            ['in' => 'yes']
        )->addAttributeToFilter('status',['in' => 'shipping'])->addAttributeToFilter('entity_id',['in' => $orderId])
            ->setOrder(
                'customer_ts',
                'desc'
        );
        
        if(!empty($orders)){
            $isDeu='Yes';
        }else{
            $isDeu='No';
        }
        return $isDeu;
    }
}
