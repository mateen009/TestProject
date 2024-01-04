<?php

namespace AscentDigital\NetsuiteConnector\Cron\Order;

use Magento\Framework\Filesystem\DirectoryList as Directory;

class GetOrder
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
     * @var Directory
     */
    protected $directory;




    /**
     * @param \Magento\Framework\App\Action\Context $context,
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        Directory $directory
    ) {
        $this->_orderRepository = $orderRepository;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->directory = $directory;
    }

    /**
     * Get SO number from Netsuite and set it in magento Controller
     */
    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/crontest.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('GetOrder cron is running');
        die('cron');
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/netsuite_cron.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('Get Order Cron Is Executed');
         //sending email to the customer
         $to = "yasirpayee02@gmail.com";
         $subject = "Get Order cron";
         
         $message = "Get Order cron run Successfully";
         
         
         $email = mail ($to,$subject,$message);
        $time = time(); // current time
        $timestart = $time;
        $to = date('Y-m-d H:i:s', $timestart);  // current time
        $lastTime = $time - (60 * 60 * 96);
        $from = date('Y-m-d H:i:s', $lastTime); // time before 24 hrs
        $orders = $this->getInternalId($to, $from);
        foreach ($orders as $order) {
            $internalId = $order->getNsInternalId();
            $soNumber = $this->getSoNumber($internalId);
            if (!empty($soNumber)) {
                $order->setNsSoNumber($soNumber);
                $order->save();
            }
        }
        $logger->info('Get Order Cron Is Finished');
    }

    /**
     * get order internal id 
     * return order collection
     */
    public function getInternalId($to, $from)
    {
        $collection = $this->_orderCollectionFactory->create()
            ->addFieldToFilter('created_at', array('to' => $to))
            ->addFieldToFilter('created_at', array('from' => $from))
            ->addFieldToFilter('ns_internal_id', array('neq' => ''))
            ->addFieldToFilter('ns_so_number', array('null' => true));
        return $collection;
    }



    /**
     *  get so number from netsuite
     */
    public function getSoNumber($internalId)
    {
        $root = $this->directory->getRoot();
        require_once($root . '/lib/PHPToolkit/NetSuiteService.php');
        $service = new \NetSuiteService();
        $service->setSearchPreferences(false, 20);
        $service->setPreferences(false, true);

        $request = new \GetRequest();
        $request->baseRef = new \RecordRef();
        $request->baseRef->internalId = $internalId;
        $request->baseRef->type = "salesOrder";
        $getResponse = $service->get($request);

        if (!$getResponse->readResponse->status->isSuccess) {
            return '';
        } else {
            $soNumber = $getResponse->readResponse->record->tranId;
            return $soNumber;
        }
    }
}
