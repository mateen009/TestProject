<?php

namespace AscentDigital\NetsuiteConnector\Cron\Order;

use AscentDigital\NetsuiteConnector\Helper\Data;


class Order {
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
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        Data $helper
    ) {
        $this->_orderRepository = $orderRepository;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->helper = $helper;
    }

    // export order to netsuite 
    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/crontest.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('Order cron is running');
        die('cron');
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/netsuite_cron.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('ORDER CRON JOB IS EXECUTED');
         //sending email to the customer
         $to = "yasirpayee02@gmail.com";
         $subject = "ORDER cron";
         
         $message = "ORDER cron run Successfully";
         
         
         $email = mail ($to,$subject,$message);
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
        $logger->info('Order Cron Job is finished.');
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
            // echo "<pre>";print_r($collection->getData());die;
        return $collection;
    }
}
