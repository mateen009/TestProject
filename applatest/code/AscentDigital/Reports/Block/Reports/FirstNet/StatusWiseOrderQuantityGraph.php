<?php

namespace AscentDigital\Reports\Block\Reports\FirstNet;

use \Magento\Framework\App\ObjectManager;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\Customer\Model\CustomerFactory;

class StatusWiseOrderQuantityGraph extends \Magento\Framework\View\Element\Template
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
    
    protected $salesRep;
    
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Sales\Model\Config\Source\Order\Status $orderStatus,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \AscentDigital\Reports\Model\SalesReps $salesRep,
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
         $this->salesRep = $salesRep;
        $this->request = $request;
        $this->_urlInterface = $urlInterface;
        $this->customerFactory = $customerFactory;
    }

    public function getQuantity($orderStatus)
    {
        $quantity=0.00;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $storeId = $storeManager->getStore()->getStoreId();
        
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
        
        $this->orders  = $this->_orderCollectionFactory->create($this->salesRep->getManagerReps($customerId))->addFieldToSelect(
            '*'
        )->addFieldToFilter('status', ['in' => $orderStatus])->addFieldToFilter('main_table.store_id', $storeId)->addExpressionFieldToSelect('total_qty_ordered', 'SUM({{total_qty_ordered}})', 'total_qty_ordered')->setOrder(
            'customer_ts',
            'desc'
        );
        foreach($this->orders->getData() as $data){
           $quantity = $data['total_qty_ordered'];
        }
        if ($quantity) {
            return $quantity;
        } else {
            return 0;}
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
