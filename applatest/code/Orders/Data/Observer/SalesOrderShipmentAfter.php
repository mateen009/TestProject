<?php

namespace Orders\Data\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesOrderShipmentAfter implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();

        $shipmentDetails = $shipment->getData();
        $_shipmentDate = $shipmentDetails['created_at'];

        /** @var \Magento\Sales\Model\Order $order */
        $order = $shipment->getOrder();
        $oid = $order->getId();

        $demoLength = $order->getData("demo_length");
        if(!$demoLength) {
            $demoLength = 0;
        }
        //echo $demoLength." : ".$_shipmentDate." : ";
        $shipmentDate = date('Y-m-d', strtotime( $_shipmentDate . " +${demoLength} days"));
       // echo $shipmentDate;die(':here');

       $order->setDueDate($shipmentDate);
       $order->save();

       //value not getting updated to sales_order_grid table using di.xml
       $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
       $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
       $connection = $resource->getConnection();
       $tableName = $resource->getTableName('sales_order_grid');
       $sql = "UPDATE $tableName SET `due_date` = '$shipmentDate' WHERE $tableName.`entity_id` = $oid";
       $connection->query($sql);

    }
}