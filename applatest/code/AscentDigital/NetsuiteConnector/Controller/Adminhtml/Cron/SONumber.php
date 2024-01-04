<?php

namespace AscentDigital\NetsuiteConnector\Controller\Adminhtml\Cron;

use Magento\Backend\App\Action;
use Magento\Framework\Filesystem\DirectoryList as Directory;

class SONumber extends Action
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


    public function __construct(
        Action\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        Directory $directory
    ) {
        $this->_orderRepository = $orderRepository;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->directory = $directory;
        parent::__construct($context);
    }

    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/customOrderCron.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('SO NUMBER CRON NUMBER IS EXECUTED');
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
        $logger->info('SO Number Cron Job is finished.');
        return;
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
        ->addFieldToFilter('ns_so_number', array('null' => false));
        
        // print_r($collection->getData());die("matee");
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
