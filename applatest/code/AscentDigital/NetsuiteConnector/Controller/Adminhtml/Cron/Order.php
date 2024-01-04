<?php

namespace AscentDigital\NetsuiteConnector\Controller\Adminhtml\Cron;

use Magento\Backend\App\Action;
use AscentDigital\NetsuiteConnector\Helper\Data;


class Order extends Action
{
   /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var \AscentDigital\NetsuiteConnector\Helper\Data
     */
    protected $helper;

    public function __construct(
        Action\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        Data $helper
    ) {
        $this->_orderRepository = $orderRepository;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/customOrderCron.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('ORDER CRON JOB IS EXECUTED');
        $time=time(); // current time
        $timestart=$time;
        $to = date('Y-m-d H:i:s', $timestart);  // time before 4 hrs
        $lastTime = $time - (60*60*24);
        $from = date('Y-m-d H:i:s', $lastTime); // time before 5 hrs
        $orders = $this->getOrderId($to, $from);
        foreach($orders as $order){
            $order = $this->_orderRepository->get($order->getId());
            for ($i = 0; $i < 2; $i++) {
                $response = $this->helper->orderToNetsuite($order);
                if ($response == 'success') {
                    break;
                }
            }

        }
        $logger->info('ORDER CRON JOB IS FINISHED');
        return ;
    }


    /**
     *  getOrderId
     * get order id by netsuite internal id
     * return order id
     */
    public function getOrderId($to, $from)
    {
        $collection = $this->_orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('created_at', array('to' => $to))
            ->addFieldToFilter('created_at', array('from' => $from))
            ->addFieldToFilter('ns_internal_id', array('null' => true))
            ->addFieldToFilter('customer_approval_status', 'approved')
            ->addFieldToFilter('approval_1_status', 'approved')
            ->addFieldToFilter('approval_2_status', 'approved')
            ->addFieldToFilter('approval_3_status', 'approved');
        return $collection;
    }
}
