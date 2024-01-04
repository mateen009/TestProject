<?php

namespace AscentDigital\Reports\Block\Adminhtml\Reports;

use Magento\Customer\Block\Account\SortLinkInterface;
use Magento\Framework\View\Element\Html\Link\Current;

class OrderTypeReport extends \Magento\Backend\Block\Template
{
    protected $_customerSession;
    protected $_orderCollectionFactory;
    protected $csvexportHelper;
    protected $request;
    

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Framework\App\Request\Http $request,
        \AscentDigital\Reports\Helper\ExportMobileCgReports $csvexportHelper,
        array $data = []
     ) {
         $this->_customerSession = $customerSession;
         $this->storeManager = $storeManager;
         $this->_orderCollectionFactory = $orderCollectionFactory;
         $this->request = $request;
         $this->csvexportHelper = $csvexportHelper;
         parent::__construct($context, $data);
     }
     
    public function getOrderCollection()
    {
        $ordersByType = array();
        $ordersByType['new'] = $this->getOrderCollectionByType('new order');
        $ordersByType['installation'] = $this->getOrderCollectionByType('installation');
        $ordersByType['return'] = $this->getOrderCollectionByType('return');
        $ordersByType['deployment'] = $this->getOrderCollectionByType('deployment');
        //echo "<pre>";print_r($ordersByType);die();
        return $ordersByType;

    }

    public function getNewOrderCollection()
    {
        $ordersByType = array();
        $ordersByType['new order'] = $this->getNewOrderCollectionByType('new order');
        $ordersByType['installation'] = $this->getNewOrderCollectionByType('installation');
        $ordersByType['return'] = $this->getNewOrderCollectionByType('return');
        $ordersByType['deployment'] = $this->getNewOrderCollectionByType('deployment');
        //echo "<pre>";print_r($ordersByType);die();
        $dataExport = $this->request->getParam('export_data');
        if(isset($dataExport)) {
            $this->csvexportHelper->exportOrderTypeRportCsv($ordersByType);
          }
        return $ordersByType;

    }

    public function getOrderByQtyCollection()
    {
        $ordersByType = array();
        $ordersByType['new'] = $this->getQtyByType('new order');
        $ordersByType['installation'] = $this->getQtyByType('installation');
        $ordersByType['return'] = $this->getQtyByType('return');
        $ordersByType['deployment'] = $this->getQtyByType('deployment');
        //echo "<pre>";print_r($ordersByType);die();
        return $ordersByType;

    }

    public function getOrderCollectionByType($type)
    {
        $collection = $this->_orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            //->addExpressionFieldToSelect('totalQty', 'SUM({{total_qty_ordered}})', 'total_qty_ordered')
            ->addFieldToFilter('order_type', $type);
        return count($collection);

    }

    public function getQtyByType($type)
    {
        $totalQty = 0;
        $collection = $this->_orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            //->addExpressionFieldToSelect('totalQty', 'SUM({{total_qty_ordered}})', 'total_qty_ordered')
            ->addFieldToFilter('order_type', $type);
        //echo $collection->getSelect()->__toString()."<br/>";
        //echo count($collection);die(':here');
        foreach($collection as $order) {
            $totalQty += $order->getTotalQtyOrdered();
        }
        return $totalQty;

    }

    public function getNewOrderCollectionByType($type)
    {
        $totalQty = 0;
        $qtyArray = array();
        $collection = $this->_orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            //->addExpressionFieldToSelect('totalQty', 'SUM({{total_qty_ordered}})', 'total_qty_ordered')
            ->addFieldToFilter('order_type', $type);
        //echo $collection->getSelect()->__toString()."<br/>";
        $totalOrders = count($collection);
        foreach($collection as $order) {
            $totalQty += $order->getTotalQtyOrdered();
        }
        $qtyArray['totalOrders'] = $totalOrders;
        $qtyArray['totalQty'] = $totalQty;
       // echo "<pre>";print_r($qtyArray);die();
        return $qtyArray;

    }
    
     
}