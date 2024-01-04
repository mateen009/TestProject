<?php
namespace Orders\Data\Ui\Component\Listing\Column;

use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;

class Data extends Column
{
    protected $_orderRepository;
    protected $_searchCriteria;

    protected $_searchCriteriaBuilder;
    protected $_shipmentRepositoryInterface;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $criteria,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepositoryInterface,
        array $components = [],
        array $data = []
    ) {
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteria  = $criteria;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_shipmentRepositoryInterface = $shipmentRepositoryInterface;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {

                $order  = $this->_orderRepository->get($item["entity_id"]);
                $orderId = $item["entity_id"];
                $demoLength = $order->getData("demo_length");
            //     $shipmentDate = '';

            //     $searchCriteria = $this->_searchCriteriaBuilder->addFilter('order_id', $orderId)->create();
            //     $shipments = $this->_shipmentRepositoryInterface->getList($searchCriteria);
            //     $shipmentData = $shipments->getItems();
            //     if ($shipmentData && count($shipmentData) > 0) {
            //       foreach ($shipmentData As $shipment) {
            //           $shipmentDetails = $shipment->getData();
            //           $_shipmentDate = $shipmentDetails['created_at'];
            //           $newDate = strtotime($_shipmentDate);
            //           $shipmentDate = date('Y-m-d', strtotime( $_shipmentDate . " +${demoLength} days"));
            //       }
            //   }
        
            //     $dueDataVal = $shipmentDate;

            $dueDataVal = $order->getData("due_date");


                // $this->getData('name') returns the name of the column so in this case it would return export_status
                $item[$this->getData('name')] = $dueDataVal;
            }
        }

        return $dataSource;
    }
}