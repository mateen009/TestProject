<?php

namespace AscentDigital\NetsuiteConnector\Controller\Adminhtml\Cron;

use Magento\Backend\App\Action;
use Magento\Sales\Api\Data\ShipmentTrackInterfaceFactory;
use Magento\Framework\Filesystem\DirectoryList as Directory;

class Shipping extends Action
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
        Action\Context $context,
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
        parent::__construct($context);
    }

    /**
     * Order Create Shipment Controller
     */
    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/customOrderCron.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('SHIPPING CRON JOB IS EXECUTED');
        $root = $this->directory->getRoot();
        require_once($root . '/lib/PHPToolkit/NetSuiteService.php');
        // get orders from netsuite
        $service = new \NetSuiteService();

        // $service->setSearchPreferences(true, 20);
        // transaction search 

        $service->setSearchPreferences(false, 20);
        $service->setPreferences(false, true);

        // type search
        $typeSearcField = new \SearchEnumMultiSelectFieldOperator();
        $typeSearcField->operator = "anyOf";
        $typeSearcField->searchValue = ["salesOrder"];

        // tracking no search
        $trackingNumberSearcField = new \SearchStringField();
        $trackingNumberSearcField->operator = "notEmpty";
        // $trackingNumberSearcField->searchValue = ["SALES_ORDER"];

        // date search
        // my custom

        $date = date("Y-m-d"); //date
        $time = date("H:i:s"); //time        
        $current_date = $date . 'T' . $time . ".000-00:00";     // current date with time
        $before_24hrs = date("Y-m-d", strtotime($date . '- 2 days')) . 'T' . $time . ".000-00:00";   //date and time before 24hrs
        // search condition
        // date
        $dateSearchField = new \SearchDateField();
        $dateSearchField->operator = "within";
        $dateSearchField->searchValue = $before_24hrs;
        $dateSearchField->searchValue2 = $current_date;


        // transaction search
        $transactionSearch = new \TransactionSearchBasic();
        // $transactionSearch->type = "SalesOrd";
        // $transactionSearch->type = $typeSearcField;
        $transactionSearch->trackingNumbers = $trackingNumberSearcField;
        $transactionSearch->lastModifiedDate = $dateSearchField;
        $request = new \SearchRequest();
        $request->searchRecord = $transactionSearch;

        $searchResponse = $service->search($request);
        // print_r($searchResponse);die('mateeen');

        if (!$searchResponse->searchResult->status->isSuccess) {
            return ;
        } else {
            $totalRecords = $searchResponse->searchResult->totalRecords;

            // echo "<pre>"; print_r($records);die;
            if ($totalRecords > 0) {
                $records = $searchResponse->searchResult->recordList->record;
                foreach ($records as $record) {
                    if (isset($record->linkedTrackingNumbers)) {
                        $orderId = $this->getOrderId($record->internalId);
                        if ($orderId) {
                            $trackingNumber = $record->linkedTrackingNumbers;
                            $title = $record->shipMethod->name;
                            $carrier = 'ups';
                            $shippment = $this->creatShipment($orderId, $trackingNumber, $carrier, $title);
                            if (!$shippment) {
                                return ;
                            }
                        } else {
                            return ;
                        }
                    } else {
                        return ;
                    }
                }
            }
            $logger->info('SHIPPING CRON JOB IS FINISHED');
            return ;
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
    public function creatShipment($orderId, $trackingNumber, $carrier, $title)
    {
        $order = $this->_orderRepository->get($orderId);

        // to check order can ship or not
        if (!$order->canShip()) {
            return false;
            // throw new \Magento\Framework\Exception\LocalizedException(
            //     __('You cant create the Shipment of this order.')
            // );
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
            return false;
        }
        return true;
    }
}
