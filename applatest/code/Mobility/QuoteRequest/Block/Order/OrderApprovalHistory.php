<?php

namespace Mobility\QuoteRequest\Block\Order;

use \Magento\Framework\App\ObjectManager;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\Customer\Model\CustomerFactory;

class OrderApprovalHistory extends \Magento\Sales\Block\Order\History
{
    /**
     * @var string
     */
    protected $_template = 'Mobility_QuoteRequest::quote/orderapprovals.phtml';

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
        parent::__construct($context, $orderCollectionFactory, $customerSession, $orderConfig, $data);
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

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $baseUrl = $storeManager->getStore()->getBaseUrl();

        if (strpos($this->request->getParam('status'), $baseUrl . 'quote/account/orderapprovals/') !== false) {
            $orderStatus = trim(str_replace($baseUrl . "quote/account/orderapprovals/", "", $this->request->getParam('status')));
        }

        if (!$orderStatus || empty($orderStatus)) {
            $orderStatus = 'Pending';
        } else {
            $orderStatus = [];
            $orderStatus[] = $this->request->getParam('status');
        }

        if (strpos($this->request->getParam('status'), $baseUrl . 'quote/account/orderapprovals/?status=') !== false) {
            $orderStatus = trim(str_replace($baseUrl . "quote/account/orderapprovals/?status=", "", $this->request->getParam('status')));
        }



        /* Get Customer id */
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }

        if (!$this->orders) {

            if (isset($orderSearch)) {
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
                )
                    ->addAttributeToFilter('created_at', array('from' => $orderFrom, 'to' => $orderTo))
                    ->setOrder(
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
                )

                    ->setOrder(
                        'created_at',
                        'desc'
                    );
            }
        }

        
        $this->orders->setPageSize($pageSize);
        $this->orders->setCurPage($page);

        return $this->orders;
    }

    //get sales reps data


    public function checkOrderStatus($customerId)
    {
        //get all reps data

        $customers = array();

        if ($this->_customerSession->getCustomerType() == 3) {
            //get sales manager reps

            $this->orders = $this->getOrderCollectionFactory()->create()->addFieldToSelect(
                '*'
            )->addAttributeToFilter('approval_1_id', $customerId)->setOrder(
                'created_at',
                'desc'
            );


            foreach ($this->orders as $data) {
                $customers[] = $data['customer_id'];
            }
            return $customers;
        } else if ($this->_customerSession->getCustomerType() == 4) {
            // get tertory manager reps

            $this->orders = $this->getOrderCollectionFactory()->create()->addFieldToSelect(
                '*'
            )->addAttributeToFilter('approval_2_id', $customerId)->setOrder(
                'created_at',
                'desc'
            );

            foreach ($this->orders as $data) {
                $customers[] = $data['customer_id'];
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

    
    
    
    
}
