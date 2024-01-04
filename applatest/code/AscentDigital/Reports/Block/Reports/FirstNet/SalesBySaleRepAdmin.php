<?php

namespace AscentDigital\Reports\Block\Reports\FirstNet;
    
use \Magento\Framework\App\ObjectManager;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\Customer\Model\CustomerFactory;

class SalesBySaleRepAdmin extends \Magento\Sales\Block\Order\History
{
    /**
     * @var string
     */

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
        \AscentDigital\Reports\Helper\ExportTotalSalesOrderReport $exportHelper,
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

    
    private function getOrderCollectionFactory()
    {
        if ($this->orderCollectionFactory === null) {
            $this->orderCollectionFactory = ObjectManager::getInstance()->get(CollectionFactoryInterface::class);
        }
        return $this->orderCollectionFactory;
    }
    
    public function getTotalQty($customerEmail){
        $qty=0.00;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $storeId = $storeManager->getStore()->getStoreId();
        
        
        $this->orders  = $this->getOrderCollectionFactory()->create()->addFieldToSelect(
            '*'
        )->addFieldToFilter(
            'status',
            array("in",'Shipping','Processing','Complete')
        )->addExpressionFieldToSelect('total_qty_ordered', 'SUM({{total_qty_ordered}})', 'total_qty_ordered')
        ->addFieldToFilter('main_table.store_id', $storeId)->addFieldToFilter(
            'customer_email',
            ['in' => $customerEmail]
        );
        
        foreach($this->orders as $order){
            $qty= $order['total_qty_ordered'];
        }
        return $qty;
    }
    
    public function getOrderCount($customerEmail){
        $orderStatus = ['shipping', 'processing','complete'];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $storeId = $storeManager->getStore()->getStoreId();
        
        $this->orders  = $this->getOrderCollectionFactory()->create()->addFieldToSelect(
            '*'
        )->addFieldToFilter(
                          'status',
                          ['in' => $orderStatus]
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
        
        $orderToDate = date('Y-m-d H:i:s');
        $orders = $this->getOrderCollectionFactory()->create()->addFieldToSelect(
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
        
        $this->orders  = $this->getOrderCollectionFactory()->create()->addFieldToSelect(
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
        
        $this->orders = $this->_orderCollectionFactory->create()->addFieldToSelect(
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

}
