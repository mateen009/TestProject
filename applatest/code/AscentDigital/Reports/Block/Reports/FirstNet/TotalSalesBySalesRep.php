<?php

namespace AscentDigital\Reports\Block\Reports\FirstNet;

use \Magento\Framework\App\ObjectManager;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\Customer\Model\CustomerFactory;

class TotalSalesBySalesRep extends \Magento\Sales\Block\Order\History
{
    /**
     * @var string
     */
    protected $_template = 'AscentDigital_Reports::reports/firstnet/total_sales_by_sales_rep.phtml';

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

    protected $exportHelper;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    protected $_urlInterface;

    protected $customerFactory;
    
    protected $generatePdf;
    
    protected $salesRep;
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
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \AscentDigital\Reports\Helper\ExportTotalSalesOrderReport $exportHelper,
        \AscentDigital\Reports\Helper\Pdf\TotalSalesBySalesRepPdf $generatePdf,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\UrlInterface $urlInterface,
        CustomerFactory $customerFactory,
        \AscentDigital\Reports\Model\SalesReps $salesRep,
        array $data = []
    ) {
        parent::__construct($context, $orderCollectionFactory, $customerSession, $orderConfig, $data);
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->_orderConfig = $orderConfig;
        $this->orderStatus = $orderStatus;
        $this->exportHelper = $exportHelper;
        $this->generatePdf = $generatePdf;
        $this->_storeManager = $storeManager;
        $this->request = $request;
        $this->_urlInterface = $urlInterface;
        $this->customerFactory = $customerFactory;
        $this->salesRep = $salesRep;
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
        return $this->_storeManager->getStore()->getUrl("quote/account/orderapprovals");
    }

    /**
     * Get customer orders
     *
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getOrders()
    {
        $orderStatus = $this->request->getParam('status');
        $orderFrom = $this->request->getParam('from');
        $orderTo = $this->request->getParam('to');
        $orderSearch = $this->request->getParam('search');
        $email = $this->request->getParam('email');
        $Orderfrom = date('Y-m-d H:i:s', strtotime($orderFrom));
        $orderExport = $this->request->getParam('export_data');
        $pdfReport = $this->request->getParam('generate_pdf');
        
        
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $baseUrl = $storeManager->getStore()->getBaseUrl();
        $storeId = $storeManager->getStore()->getStoreId();
        
        /* Get Customer id */
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
        
         if (!$this->orders) {
                $this->orders  = $this->_orderCollectionFactory->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
                               '*'
                           )->addFieldToFilter(
                               'status',
                               array("in",'Shipping','Processing','Complete')
                           )->addFieldToFilter('main_table.store_id', $storeId)->addExpressionFieldToSelect('total_qty_ordered', 'SUM({{total_qty_ordered}})', 'total_qty_ordered')->setOrder(
                               'customer_ts',
                               'desc'
                           );
              $this->orders->getSelect()->group('customer_email');
       }

        if(isset($orderExport)){
           $this->exportHelper->exportData($this->orders);
        }

        if(isset($pdfReport)){
           $this->generatePdf->generate($this->orders);
        }
        return $this->orders;
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
            )->setAvailableLimit([ 10 => 10, 15 => 15, 20 => 20])
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
    
    public function getCustomerType(){
        return $this->_customerSession->getCustomerType();
    }
}
