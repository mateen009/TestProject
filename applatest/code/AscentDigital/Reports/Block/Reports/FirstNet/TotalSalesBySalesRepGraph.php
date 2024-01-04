<?php

namespace AscentDigital\Reports\Block\Reports\FirstNet;

use \Magento\Framework\App\ObjectManager;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\Customer\Model\CustomerFactory;

class TotalSalesBySalesRepGraph extends \Magento\Framework\View\Element\Template
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

    
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    protected $_urlInterface;

    protected $customerFactory;
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
        
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\UrlInterface $urlInterface,
        CustomerFactory $customerFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->_orderConfig = $orderConfig;
        $this->orderStatus = $orderStatus;
        $this->_storeManager = $storeManager;
        $this->request = $request;
        $this->_urlInterface = $urlInterface;
        $this->customerFactory = $customerFactory;
    }

    public function getAllOrderStatus()
    {
        return $this->orderStatus->toOptionArray();
    }

    public function getCurrentURL()
    {
        return $this->_urlInterface->getCurrentUrl();
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
        $orderTo = date('Y-m-d H:i:s', strtotime($orderTo . ' +1 day'));
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $baseUrl = $storeManager->getStore()->getBaseUrl();
        $storeId = $storeManager->getStore()->getStoreId();
        /* Get Customer id */
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }

        
         if (!$this->orders) {
                $this->orders  = $this->getOrderCollectionFactory()->create($this->getManagerReps($customerId))->addFieldToSelect(
                               '*'
                           )->addFieldToFilter('main_table.store_id', $storeId)->addFieldToFilter(
                               'status',
                               array("in",'Shipping','Processing','Complete')
                           )->addExpressionFieldToSelect('grand_total', 'SUM({{grand_total}})', 'grand_total')->addExpressionFieldToSelect('total_qty_ordered', 'SUM({{total_qty_ordered}})', 'total_qty_ordered')->setOrder(
                               'created_at',
                               'desc'
                           );
              $this->orders->getSelect()->group('customer_email');
       }

        return $this->orders;
    }

    public function getOrderCount($customerEmail){
        
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
        
        $this->orders  = $this->getOrderCollectionFactory()->create($this->getManagerReps($customerId))->addFieldToSelect(
            '*'
        )->addFieldToFilter(
            'status',
            array("in",'Shipping','Processing','Complete')
        )->addFieldToFilter(
            'customer_email',
            ['in' => $customerEmail]
        );
        $count = count($this->orders);
        return $count;
    }
    
    public function isFirstNet()
    {
        $currentWebsiteId = $this->_storeManager->getStore()->getWebsiteId();
        if ($currentWebsiteId == '3') {
            return true;
        }
        return false;
    }
    
    
    public function allOrders($orderFrom,$orderTo,$email,$customerId){
        die('here');
        
        if($orderFrom!='' && $orderTo!='' && $email!=''){
            $this->orders = $this->getOrderCollectionFactory()->create($this->getManagerReps($customerId))->addFieldToSelect(
                '*'
            )->addFieldToFilter('main_table.store_id', $storeId)->addFieldToFilter(
                'status',
                array("in",'Shipping','Processing','Complete')
            )->addAttributeToFilter('customer_ts', array('from' => $orderFrom, 'to' => $orderTo))->addAttributeToFilter('customer_email',['in' => $email])
                ->setOrder(
                    'created_at',
                    'desc'
                );
            
        }else if($orderFrom=='' && $orderTo=='' && $email!=''){
            $this->orders = $this->getOrderCollectionFactory()->create($this->getManagerReps($customerId))->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'status',
                array("in",'Shipping','Processing','Complete')
            )->addFieldToFilter('main_table.store_id', $storeId)->addAttributeToFilter('customer_email',['in' => $email])
                ->setOrder(
                    'created_at',
                    'desc'
                );
            
        }else if($orderFrom!='' && $orderTo!='' && $email==''){
            $this->orders = $this->getOrderCollectionFactory()->create($this->getManagerReps($customerId))->addFieldToSelect(
                '*'
            )->addFieldToFilter('main_table.store_id', $storeId)->addFieldToFilter(
                'status',
                array("in",'Shipping','Processing','Complete')
            )->addAttributeToFilter('customer_ts', array('from' => $orderFrom, 'to' => $orderTo))->setOrder(
                    'created_at',
                    'desc'
                );
            
        }else if($orderFrom=='' && $orderTo=='' && $email==''){
            $this->orders = $this->getOrderCollectionFactory()->create($this->getManagerReps($customerId))->addFieldToSelect(
                '*'
            )->addFieldToFilter('main_table.store_id', $storeId)->addExpressionFieldToSelect('grand_total', 'SUM({{grand_total}})', 'grand_total')->addExpressionFieldToSelect('total_qty_ordered', 'SUM({{total_qty_ordered}})', 'total_qty_ordered')->setOrder(
                    'created_at',
                    'desc'
                );
            $this->orders = $this->getOrderCollectionFactory()->getSelect()->group('customer_id');
        }
        
        return $this->orders;
        
    }
    
    public function statusBaseFilters($orderStatus,$orderFrom,$orderTo,$email,$customerId){
        
        die('here');
        if($orderFrom!='' && $orderTo!='' && $email!=''){
           $this->orders = $this->getOrderCollectionFactory()->create($this->getManagerReps($customerId))->addFieldToSelect(
               '*'
           )->addFieldToFilter(
               'status',
               array("in",'Shipping','Processing','Complete')
           )->addAttributeToFilter('customer_ts', array('from' => $orderFrom, 'to' => $orderTo))
               ->addFieldToFilter(
                   'status',
                   ['in' => $orderStatus]
               )->addAttributeToFilter('customer_email',['in' => $email])
               ->setOrder(
                   'created_at',
                   'desc'
               );
            
        }else if($orderFrom=='' && $orderTo=='' && $email!=''){
           $this->orders = $this->getOrderCollectionFactory()->create($this->getManagerReps($customerId))->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'status',
                array("in",'Shipping','Processing','Complete')
            )->addFieldToFilter(
                    'status',
                    ['in' => $orderStatus]
                )->addAttributeToFilter('customer_email',['in' => $email])
                ->setOrder(
                    'created_at',
                    'desc'
                );
        }else if($orderFrom!='' && $orderTo!='' && $email==''){
            $this->orders = $this->getOrderCollectionFactory()->create($this->getManagerReps($customerId))->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'status',
                array("in",'Shipping','Processing','Complete')
            )->addAttributeToFilter('customer_ts', array('from' => $orderFrom, 'to' => $orderTo))
                ->addFieldToFilter(
                    'status',
                    ['in' => $orderStatus]
                )
                ->setOrder(
                    'created_at',
                    'desc'
                );
        }else if($orderFrom=='' && $orderTo=='' && $email==''){
            $this->orders = $this->getOrderCollectionFactory()->create($this->getManagerReps($customerId))->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'status',
                array("in",'Shipping','Processing','Complete')
            )->addFieldToFilter(
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
    
    
    //get sales reps data


    public function getManagerReps($customerId)
    {
        $firstnet = $this->isFirstNet();
        if(!$firstnet){
            return $customerId;
        }
        //get all reps data
        $customers = array();
        if ($this->_customerSession->getCustomerType() == 1) {
            return $customerId;
        } else if ($this->_customerSession->getCustomerType() == 3) {
            //get sales manager reps
            $customerData = $this->customerFactory->create()->getCollection()->addAttributeToSelect("entity_id")
                ->addAttributeToFilter("SalesManager_ID", $customerId)->load();
            foreach ($customerData->getData() as $data) {
                $customers[] = $data['entity_id'];
            }
            return $customers;
        } else if ($this->_customerSession->getCustomerType() == 4) {
            // get tertory manager reps
            $customerData = $this->customerFactory->create()->getCollection()->addAttributeToSelect("entity_id")
                ->addAttributeToFilter("TerritoryManager_ID", $customerId)->load();
            foreach ($customerData->getData() as $data) {
                $customers[] = $data['entity_id'];
            }
            return $customers;
        } else if ($this->_customerSession->getCustomerType() == 5) {
            // get executive manager reps
            $customerData = $this->customerFactory->create()->getCollection()->addAttributeToSelect("entity_id")
                ->addAttributeToFilter("Executive_ID", $customerId)->load();
            foreach ($customerData->getData() as $data) {
                $customers[] = $data['entity_id'];
            }
            return $customers;
        }
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

}
