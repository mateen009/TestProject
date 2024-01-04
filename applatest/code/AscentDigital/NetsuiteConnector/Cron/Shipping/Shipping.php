<?php
// CHM-MA



namespace AscentDigital\NetsuiteConnector\Cron\Shipping;

use Magento\Sales\Api\Data\ShipmentTrackInterfaceFactory;
use Magento\Framework\Filesystem\DirectoryList as Directory;

class Shipping
{

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var \Magento\Sales\Model\Convert\Order
     */
    protected $_convertOrder;

    /**
     * @var \Magento\Shipping\Model\ShipmentNotifier
     */
    protected $_shipmentNotifier;

    /**
     * @var ShipmentTrackInterfaceFactory
     */

    private $trackFactory;

    /**
     * @var CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $directory;

    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Convert\Order $convertOrder,
        \Magento\Shipping\Model\ShipmentNotifier $shipmentNotifier,
        ShipmentTrackInterfaceFactory $trackFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        Directory $directory
    ) {
        $this->_orderRepository = $orderRepository;
        $this->_convertOrder = $convertOrder;
        $this->_shipmentNotifier = $shipmentNotifier;
        $this->trackFactory = $trackFactory;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->directory = $directory;
    }

    /**
     * Order Create Shipment Controller
     */
    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/crontest.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('Shipping cron is running');
        die('cron');
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/netsuite_cron.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('SHIPPING CRON JOB IS EXECUTED');
         //sending email to the customer
         $to = "yasirpayee02@gmail.com";
         $subject = "SHIPPING cron";
         
         $message = "SHIPPING cron run Successfully";
         
         
         $email = mail ($to,$subject,$message);
        $root = $this->directory->getRoot();
        require_once($root . '/lib/PHPToolkit/NetSuiteService.php');
        // get orders from netsuite
        $service = new \NetSuiteService();
        $service->setSearchPreferences(false, '20');

        // get item fulfilment list from netsuite
        $search = new \TransactionSearchBasic();
        $search->type = new \SearchEnumMultiSelectField();
        $search->type->searchValue = array('_itemFulfillment');
        $search->type->operator = 'anyOf';
        $date = date("Y-m-d"); //date
        $time = date("H:i:s"); //time        
        $current_date = $date . 'T' . $time;     // current date with time
        $before_24hrs = date("Y-m-d", strtotime($date . '- 1 day')) . 'T' . $time;   //date and time before 24hrs
        // date search
        $dateSearchField = new \SearchDateField();
        $dateSearchField->operator = "within";
        $dateSearchField->searchValue = $before_24hrs;
        $dateSearchField->searchValue2 = $current_date;

        $search->lastModifiedDate = $dateSearchField;

        $request = new \SearchRequest();
        $request->searchRecord = $search;

        $searchResponse = $service->search($request);

        if (!$searchResponse->searchResult->status->isSuccess) {
            return;
        } else {
            $totalRecords = $searchResponse->searchResult->totalRecords;

            if ($totalRecords > 0) {
                $records = $searchResponse->searchResult->recordList->record;
                foreach ($records as $record) {
                    $InternalId = $record->createdFrom->internalId;
                    $orderId = $this->getOrderId($InternalId);
                    if ($orderId) {
                        $packages = $record->packageList->package;
                        $trackingNumbers = [];
                        foreach ($packages as $package) {
                            $trackingNumbers[] = $package->packageTrackingNumber;
                        }
                        $title = $record->shipMethod->name;
                        $carrier = 'custom';
                        $this->creatShipment($orderId, $trackingNumbers, $carrier, $title, $logger);
                    }
                }
            }
            $logger->info('SHIPPING CRON JOB IS FINISHED');
            return;
        }

        // end order from netsuite
    }

    /**
     *  getOrderId
     * get order id by netsuite internal id
     * return order id
     */
    public function getOrderId($internalId)
    {
        $collection = $this->_orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('ns_internal_id', $internalId)->getFirstItem();
        if ($collection->getData()) {
            return $collection->getId();
        } else {
            return false;
        }
    }

    /**
     * creatShipment
     * convert order to shippment
     */
    public function creatShipment($orderId, $trackingNumbers, $carrier, $title, $logger)
    {
        $order = $this->_orderRepository->get($orderId);

        // to check order can ship or not
        if (!$order->canShip()) {
            return false;
        }

        $orderShipment = $this->_convertOrder->toShipment($order);

        foreach ($order->getAllItems() as $orderItem) {

            // Check virtual item and item Quantity
            if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                continue;
            }

            $qty = $orderItem->getQtyToShip();
            $shipmentItem = $this->_convertOrder->itemToShipmentItem($orderItem)->setQty($qty);

            $orderShipment->addItem($shipmentItem);
        }

        $orderShipment->register();
        $orderShipment->getOrder()->setIsInProcess(true);
        foreach ($trackingNumbers as $trackingNumber) {
            try {
                // add tracking no
                $track = $this->trackFactory->create()->setNumber(
                    $trackingNumber
                )->setCarrierCode(
                    $carrier
                )->setTitle(
                    $title
                );
                $orderShipment->addTrack($track);
                // Save created Order Shipment
                $orderShipment->save();
                $orderShipment->getOrder()->save();

                // Send Shipment Email
                $this->_shipmentNotifier->notify($orderShipment);
                $orderShipment->save();
            } catch (\Exception $e) {
                $logger->debug(__('AscentDigital\NetsuiteConnector\Cron\Shipping\Shipping'.$e->getMessage()));
            }
        }
        return true;
    }
}
