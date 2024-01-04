<?php
namespace Mobility\QuoteRequest\Block\Quote\Account;

use Magento\Framework\View\Element\Template;
use Mobility\QuoteRequest\Api\Data\QuoteRequestInterface;
use Mobility\QuoteRequest\Api\QuoteRequestRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use \Magento\Framework\App\ObjectManager;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\Customer\Model\CustomerFactory;
/**
 * Main quote Approval block
 */
class OrderApprovals extends Template
{
    /**
     * @var QuoteRequestRepositoryInterface
     */
    private $quoteRequestRepository;

    
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
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @param Template\Context $context
     * @param QuoteRequestRepositoryInterface $quoteRequestRepository
     * @param CheckoutSession $checkoutSession
     * @param CustomerSession $customerSession
     * @param array $data
     */
    public function __construct(
        QuoteRequestRepositoryInterface $quoteRequestRepository,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Sales\Model\Config\Source\Order\Status $orderStatus,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\UrlInterface $urlInterface,
        CustomerFactory $customerFactory,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->quoteRequestRepository = $quoteRequestRepository;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_orderConfig = $orderConfig;
        $this->orderStatus = $orderStatus;
        $this->_storeManager = $storeManager;
        $this->request = $request;
        $this->_urlInterface = $urlInterface;
        $this->customerFactory = $customerFactory;
    }

    /**
     * Returns action url for quote form
     *
     * @return string
     */
    public function getStatusAction($id, $status)
    {
        return $this->getUrl('quote/status/update', ['_secure' => true, 'id' => $id, 'status' => $status]);
    }

    /**
     * Returns custmomer type
     *
     * @return int
     */
    public function getCustomerType()
    {
        return (int) $this->customerSession->getCustomerType();
    }

    /**
     * Fetch Customer Quote Request List
     *
     * @return mixed
     */
    public function getCustomerQuoteRequestList()
    {
        $quote = $this->checkoutSession->getQuote();
        if($this->customerSession->getCustomerType() == 3) {
            $requestQuoteCollection = 'approved';
        } else if($this->customerSession->getCustomerType() == 4) {
            $requestQuoteCollection = 'approved';
        } else {
            $requestQuoteCollection =  'approved';
        }
        
        return $requestQuoteCollection;
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

        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
            $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest(
                
            )->getParam('limit') : 10;
            
            
        
        $orderStatus = $this->request->getParam('status');
        $orderFrom = $this->request->getParam('from');
        $orderTo = $this->request->getParam('to');
        $orderSearch = $this->request->getParam('search');
        $Orderfrom = date('Y-m-d H:i:s', strtotime($orderFrom));
        $orderTo = date('Y-m-d H:i:s', strtotime($orderTo . ' +1 day'));
        $email = $this->request->getParam('email');
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $storeId = $storeManager->getStore()->getStoreId();
        
        $baseUrl = $storeManager->getStore()->getBaseUrl();

        if (strpos($this->request->getParam('status'), $baseUrl . 'quote/account/orderapprovals/') !== false) {
            $orderStatus = trim(str_replace($baseUrl . "quote/account/orderapprovals/", "", $this->request->getParam('status')));
        }

        if (!$orderStatus || empty($orderStatus)) {
            $orderStatus = 'All';
        } else {
            $orderStatus = [];
            $orderStatus[] = $this->request->getParam('status');
        }

        if (strpos($this->request->getParam('status'), $baseUrl . 'quote/account/orderapprovals/?status=') !== false) {
            $orderStatus = trim(str_replace($baseUrl . "quote/account/orderapprovals/?status=", "", $this->request->getParam('status')));
        }
        

        /* Get Customer id */
        if (!($customerId = $this->customerSession->getCustomerId())) {
            return false;
        }

        $storeId = $storeManager->getStore()->getStoreId();
       
        if (!$this->orders) {
            if (isset($orderSearch)) {
                if ($orderStatus ==  'All') {
                    
                    $this->orders = $this->allOrders($orderFrom,$this->request->getParam('to'),$email,$customerId,$storeId);
                    
                }else{
                    
                    $this->orders = $this->statusBaseFilters($orderFrom,$this->request->getParam('to'),$customerId,$orderStatus,$email,$storeId);
                }
                
                
            } else {
               
               if ($orderStatus ==  'All') {
                   
                   $this->orders = $this->getOrderCollectionFactory()->create($this->checkOrderStatus($customerId))->addFieldToSelect(
                                 '*'
                             )->addAttributeToFilter(
                                 'approval_1_status',
                                 ['nin' => '0']
                             )->addAttributeToFilter(
                                 'approval_2_status',
                                 ['nin' => '0']
                             )->addFieldToFilter('store_id', $storeId)->setOrder(
                                     'created_at',
                                     'desc'
                                 );
               } else {
                  
                    $this->orders = $this->getOrderCollectionFactory()->create($this->checkOrderStatus($customerId))->addFieldToSelect(
                                 '*'
                             )->addFieldToFilter(
                                 'status',
                                 ['in' => $orderStatus]
                             )->addAttributeToFilter(
                                 'approval_1_status',
                                 ['nin' => '0']
                             )->addAttributeToFilter(
                                 'approval_2_status',
                                 ['nin' => '0']
                             )->addFieldToFilter('store_id', $storeId)->setOrder(
                                     'created_at',
                                     'desc'
                                 );
               }
            }
        }
        

        
        $this->orders->setPageSize($pageSize);
        $this->orders->setCurPage($page);
        
        return $this->orders;
    }

    
    public function isFirstNet()
    {
        $currentWebsiteId = $this->_storeManager->getStore()->getWebsiteId();
        if ($currentWebsiteId == '3') {
            return true;
        }
        return false;
    }
    
    //get sales reps data


    public function checkOrderStatus($customerId)
    {
        //get all reps data

       $firstnet = $this->isFirstNet();
       if(!$firstnet){
           return $customerId;
       }
       //get all reps data
       $customers = array();
       if ($this->customerSession->getCustomerType() == 1) {
           return $customerId;
       } else if ($this->customerSession->getCustomerType() == 3) {
           $customers[]=$customerId;
           //get sales manager reps
           $customerData = $this->customerFactory->create()->getCollection()->addAttributeToSelect("entity_id")
               ->addAttributeToFilter("SalesManager_ID", $customerId)->load();
           foreach ($customerData->getData() as $data) {
               $customers[] = $data['entity_id'];
           }
           return $customers;
       } else if ($this->customerSession->getCustomerType() == 4) {
           $customers[]=$customerId;
           // get tertory manager reps
           $customerData = $this->customerFactory->create()->getCollection()->addAttributeToSelect("entity_id")
               ->addAttributeToFilter("TerritoryManager_ID", $customerId)->load();
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
            )->setAvailableLimit([5 => 5, 10 => 10, 15 => 15, 20 => 20])
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
    
   
    
    
    public function allOrders($orderFrom,$orderTo,$email,$customerId,$storeId){
        
         if($orderFrom=='' && $orderTo=='' && $email!=''){
            
           $this->orders = $this->getOrderCollectionFactory()->create($this->checkOrderStatus($customerId))->addFieldToSelect('*')->addAttributeToFilter(
                 'approval_1_status',['nin' => '0'])->addAttributeToFilter('approval_2_status',['nin' => '0'])->addAttributeToFilter('customer_email',['in' => $email])->addFieldToFilter('store_id', $storeId)->setOrder('created_at','desc');
            
            
            
        }else if($orderFrom!='' && $orderTo!='' && $email==''){
            $this->orders = $this->getOrderCollectionFactory()->create($this->checkOrderStatus($customerId))->addFieldToSelect('*')->addAttributeToFilter(
                            'approval_1_status',['nin' => '0'])->addAttributeToFilter('approval_2_status',['nin' => '0'])->addAttributeToFilter('customer_ts', array('from' => $orderFrom, 'to' => $orderTo))->addFieldToFilter('store_id', $storeId)->setOrder('created_at','desc');
            
        }else if($orderFrom=='' && $orderTo=='' && $email==''){
             $this->orders = $this->getOrderCollectionFactory()->create($this->checkOrderStatus($customerId))->addFieldToSelect('*')->addAttributeToFilter(
                            'approval_1_status',['nin' => '0'])->addAttributeToFilter('approval_2_status',['nin' => '0'])->addFieldToFilter('store_id', $storeId)->setOrder('created_at','desc');
            
        }else if($orderFrom!='' && $orderTo!='' && $email!=''){
            $this->orders = $this->getOrderCollectionFactory()->create($this->checkOrderStatus($customerId))->addFieldToSelect('*')->addAttributeToFilter(
                            'approval_1_status',['nin' => '0'])->addAttributeToFilter('approval_2_status',['nin' => '0'])->addAttributeToFilter('customer_email',['in' => $email])->addAttributeToFilter('customer_ts', array('from' => $orderFrom, 'to' => $orderTo))->addFieldToFilter('store_id', $storeId)->setOrder('created_at','desc');
            
        }
        
        return $this->orders;
        
    }
    
    
    
    public function statusBaseFilters($orderFrom,$orderTo,$customerId,$orderStatus,$email,$storeId){
        
        if($orderFrom=='' && $orderTo=='' && $email==''){
            
           $this->orders = $this->getOrderCollectionFactory()->create($this->checkOrderStatus($customerId))->addFieldToSelect(
               '*'
           )->addFieldToFilter(
               'status',
               ['in' => $orderStatus]
           )->addAttributeToFilter(
               'approval_2_status',
               ['nin' => '0']
           )->addFieldToFilter('store_id', $storeId)->setOrder(
                   'created_at',
                   'desc'
               );
        }else if($orderFrom=='' && $orderTo=='' && $email!=''){
            
           $this->orders = $this->getOrderCollectionFactory()->create($this->checkOrderStatus($customerId))->addFieldToSelect(
               '*'
           )->addFieldToFilter(
               'status',
               ['in' => $orderStatus]
           )->addFieldToFilter('store_id', $storeId)->addAttributeToFilter('customer_email',['in' => $email])->addAttributeToFilter(
               'approval_2_status',
               ['nin' => '0']
           )->setOrder(
                   'created_at',
                   'desc'
               );
        }else if($orderFrom!='' && $orderTo!='' && $email==''){
            
           $this->orders = $this->getOrderCollectionFactory()->create($this->checkOrderStatus($customerId))->addFieldToSelect(
               '*'
           )->addFieldToFilter(
               'status',
               ['in' => $orderStatus]
           )->addFieldToFilter('store_id', $storeId)->addAttributeToFilter('customer_ts', array('from' => $orderFrom, 'to' => $orderTo))->addAttributeToFilter(
               'approval_1_status',
               ['nin' => '0']
           )->addAttributeToFilter(
               'approval_2_status',
               ['nin' => '0']
           )->setOrder(
                   'created_at',
                   'desc'
               );
        }else if($orderFrom!='' && $orderTo!='' && $email!=''){
            
           $this->orders = $this->getOrderCollectionFactory()->create($this->checkOrderStatus($customerId))->addFieldToSelect(
               '*'
           )->addAttributeToFilter('customer_email',['in' => $email])->addAttributeToFilter('customer_ts', array('from' => $orderFrom, 'to' => $orderTo))->addAttributeToFilter(
               'approval_1_status',
               ['nin' => '0']
           )->addFieldToFilter('store_id', $storeId)->addAttributeToFilter(
               'approval_2_status',
               ['nin' => '0']
           )->setOrder(
                   'created_at',
                   'desc'
               );
        }
        return $this->orders;
    }
}
