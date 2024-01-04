<?php

namespace AscentDigital\Reports\Block\Reports\FirstNet;

use \Magento\Framework\App\ObjectManager;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\Customer\Model\CustomerFactory;

class OrderStatusReportGraph extends \Magento\Framework\View\Element\Template
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
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Sales\Model\Config\Source\Order\Status $orderStatus,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \AscentDigital\Reports\Helper\ExportTotalSalesOrderReport $exportHelper,
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
        $this->exportHelper = $exportHelper;
        $this->_storeManager = $storeManager;
        $this->request = $request;
        $this->_urlInterface = $urlInterface;
        $this->customerFactory = $customerFactory;
    }

    public function isFirstNet()
    {
        $currentWebsiteId = $this->_storeManager->getStore()->getWebsiteId();
        if ($currentWebsiteId == '3') {
            return true;
        }
        return false;
    }

    public function getOrders($orderStatus)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $storeId = $storeManager->getStore()->getStoreId();
        
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
        $this->orders = $this->getOrderCollectionFactory()->create($this->getManagerReps($customerId))
        ->addFieldToSelect('*')->addFieldToFilter('status', ['in' => $orderStatus])->addFieldToFilter('main_table.store_id', $storeId);
        
        return  count($this->orders);
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
            $customers[]=$customerId;
            //get sales manager reps
            $customerData = $this->customerFactory->create()->getCollection()->addAttributeToSelect("entity_id")
                ->addAttributeToFilter("SalesManager_ID", $customerId)->load();
            foreach ($customerData->getData() as $data) {
                $customers[] = $data['entity_id'];
            }
            return $customers;
        } else if ($this->_customerSession->getCustomerType() == 4) {
            $customers[]=$customerId;
            // get tertory manager reps
            $customerData = $this->customerFactory->create()->getCollection()->addAttributeToSelect("entity_id")
                ->addAttributeToFilter("TerritoryManager_ID", $customerId)->load();
            foreach ($customerData->getData() as $data) {
                $customers[] = $data['entity_id'];
            }
            return $customers;
        } else if ($this->_customerSession->getCustomerType() == 5) {
            $customers[]=$customerId;
            // get tertory manager reps
            $customerData = $this->customerFactory->create()->getCollection()->addAttributeToSelect("entity_id")
                ->addAttributeToFilter("Executive_ID", $customerId)->load();
            foreach ($customerData->getData() as $data) {
                $customers[] = $data['entity_id'];
            }
            return $customers;
        }
    }

    private function getOrderCollectionFactory()
    {
        if ($this->orderCollectionFactory === null) {
            $this->orderCollectionFactory = ObjectManager::getInstance()->get(CollectionFactoryInterface::class);
        }
        return $this->orderCollectionFactory;
    }

    public function getEmptyOrdersMessage()
    {
        return __('You have placed no orders or no any selected order status found.');
    }
    
    public function getCustomerType()
    {
        $this->_customerSession->getCustomerType();
    }

}
