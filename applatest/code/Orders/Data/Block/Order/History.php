<?php

namespace Orders\Data\Block\Order;

use \Magento\Framework\App\ObjectManager;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\Customer\Model\CustomerFactory;

class History extends \Magento\Sales\Block\Order\History
{
    /**
     * @var string
     */
    protected $_template = 'Orders_Data::order/history.phtml';

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
        \AscentDigital\Reports\Model\SalesReps $salesRep,
        \Magento\Sales\Model\Config\Source\Order\Status $orderStatus,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\UrlInterface $urlInterface,
        CustomerFactory $customerFactory,
        array $data = []
    ) {
        parent::__construct($context, $orderCollectionFactory, $customerSession, $orderConfig, $data);
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->_orderConfig = $orderConfig;
        $this->orderStatus = $orderStatus;
        $this->salesRep = $salesRep;
        $this->_storeManager = $storeManager;
        $this->request = $request;
        $this->_urlInterface = $urlInterface;
        $this->customerFactory = $customerFactory;
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
        return $this->_storeManager->getStore()->getUrl("sales/order/history");
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
        // $Orderfrom = date('Y-m-d H:i:s', strtotime($orderFrom));
        $orderTo = date('Y-m-d H:i:s', strtotime($orderTo . ' +1 day'));
        $email = $this->request->getParam('email');

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $baseUrl = $storeManager->getStore()->getBaseUrl();
        $storeId = $storeManager->getStore()->getStoreId();
        if($this->request->getParam('status')){
        if (strpos($this->request->getParam('status'), $baseUrl.'sales/order/history/') !== false) {
            $orderStatus = trim(str_replace($baseUrl."sales/order/history/", "", $this->request->getParam('status')));
        }}

        if (!$orderStatus || empty($orderStatus)) {
            $orderStatus = 'All';
        } else {
            $orderStatus = [];
            $orderStatus[] = $this->request->getParam('status');
        }
        if($this->request->getParam('status')){
        if (strpos($this->request->getParam('status'), $baseUrl.'sales/order/history/?status=') !== false) {
            $orderStatus = trim(str_replace($baseUrl."sales/order/history/?status=", "", $this->request->getParam('status')));
        }
    }

        /* Get Customer id */
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
        

        if (!$this->orders) {
            if (isset($orderSearch)) {
                if ($orderStatus ==  'All') {
                    
                   $this->orders = $this->allOrders($orderFrom,$this->request->getParam('to'),$email,$customerId,$storeId);
                    
                } else {
                   $this->orders = $this->statusBaseFilters($orderStatus,$orderFrom,$this->request->getParam('to'),$email,$customerId,$storeId);
                    
                }
            } else {
                if ($orderStatus ==  'All') {
                    $this->orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
                        '*'
                    )->addFieldToFilter('main_table.store_id', $storeId)->setOrder(
                        'created_at',
                        'desc'
                    );
                } else {
                    $this->orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
                        '*'
                    )->addFieldToFilter('main_table.store_id', $storeId)->addFieldToFilter(
                        'status',
                        ['in' => $orderStatus]
                    )->setOrder(
                        'created_at',
                        'desc'
                    );
                }

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
    
    public function getCustomerType()
    {
        $this->_customerSession->getCustomerType();
    }
    
    
    public function allOrders($orderFrom,$orderTo,$email,$customerId,$storeId){
        
        if($orderFrom!='' && $orderTo!='' && $email!=''){
            $this->orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
                '*'
            )->addAttributeToFilter('customer_ts', array('from' => $orderFrom, 'to' => $orderTo))->addFieldToFilter('main_table.store_id', $storeId)->addAttributeToFilter('customer_email',['in' => $email])
                ->setOrder(
                    'created_at',
                    'desc'
                );
            
        }else if($orderFrom=='' && $orderTo=='' && $email!=''){
            $this->orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
                '*'
            )->addFieldToFilter('main_table.store_id', $storeId)->addAttributeToFilter('customer_email',['in' => $email])
                ->setOrder(
                    'created_at',
                    'desc'
                );
            
        }else if($orderFrom!='' && $orderTo!='' && $email==''){
            $this->orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
                '*'
            )->addFieldToFilter('main_table.store_id', $storeId)->addAttributeToFilter('customer_ts', array('from' => $orderFrom, 'to' => $orderTo))->setOrder(
                    'created_at',
                    'desc'
                );
            
        }else if($orderFrom=='' && $orderTo=='' && $email==''){
            $this->orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
                '*'
            )->addFieldToFilter('main_table.store_id', $storeId)->setOrder(
                    'created_at',
                    'desc'
                );
            
        }
        
        return $this->orders;
        
    }
    
    public function statusBaseFilters($orderStatus,$orderFrom,$orderTo,$email,$customerId,$storeId){
        
        
        if($orderFrom!='' && $orderTo!='' && $email!=''){
           $this->orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
               '*'
           )->addAttributeToFilter('customer_ts', array('from' => $orderFrom, 'to' => $orderTo))
               ->addFieldToFilter(
                   'status',
                   ['in' => $orderStatus]
               )->addFieldToFilter('main_table.store_id', $storeId)->addAttributeToFilter('customer_email',['in' => $email])
               ->setOrder(
                   'created_at',
                   'desc'
               );
            
        }else if($orderFrom=='' && $orderTo=='' && $email!=''){
           $this->orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
                '*'
            )->addFieldToFilter('main_table.store_id', $storeId)->addFieldToFilter(
                    'status',
                    ['in' => $orderStatus]
                )->addAttributeToFilter('customer_email',['in' => $email])
                ->setOrder(
                    'created_at',
                    'desc'
                );
        }else if($orderFrom!='' && $orderTo!='' && $email==''){
            $this->orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
                '*'
            )->addFieldToFilter('main_table.store_id', $storeId)->addAttributeToFilter('customer_ts', array('from' => $orderFrom, 'to' => $orderTo))
                ->addFieldToFilter(
                    'status',
                    ['in' => $orderStatus]
                )
                ->setOrder(
                    'created_at',
                    'desc'
                );
        }else if($orderFrom=='' && $orderTo=='' && $email==''){
            $this->orders = $this->getOrderCollectionFactory()->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
                '*'
            )->addFieldToFilter('main_table.store_id', $storeId)->addFieldToFilter(
                    'status',
                    ['in' => $orderStatus]
                )
                ->setOrder(
                    'created_at',
                    'desc'
                );
        }
        return  $this->orders;
    }
}
