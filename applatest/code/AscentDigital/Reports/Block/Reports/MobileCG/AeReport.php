<?php

namespace AscentDigital\Reports\Block\Reports\MobileCg;

use Magento\Customer\Block\Account\SortLinkInterface;
use Magento\Framework\View\Element\Html\Link\Current;

class AeReport extends Current
{
    protected $_customerSession;
    protected $_orderCollectionFactory;
    protected $csvexportHelper;
    protected $request;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
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
         $this->csvexportHelper = $csvexportHelper;
         $this->request = $request;
         parent::__construct($context, $defaultPath, $data);
     }

    public function getAllTypeOrders()
    {
      $orderTypes = "new order|installation|return|deployment";
        $collection = $this->_orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('order_type', array(array('regexp' => $orderTypes)));
          //echo $collection->getSelect()->__toString();die();
          
        return $collection;

    }
     
    public function getOrderCollection()
    {
        $ordersByType = array();
        $ordersByType['new order'] = $this->getOrderQtyByType('new order');
        $ordersByType['installation'] = $this->getOrderQtyByType('installation');
        $ordersByType['return'] = $this->getOrderQtyByType('return');
        $ordersByType['deployment'] = $this->getOrderQtyByType('deployment');
        //echo "<pre>";print_r($ordersByType);die();
        $dataExport = $this->request->getParam('export_data');
        //echo "<pre>";print_r($_REQUEST);echo "</pre>";
        if(!isset($_GET['otype']) && isset($dataExport)) {
            $this->csvexportHelper->exportAERportCsv($ordersByType);
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

    public function getOrderQtyByType($type)
    {
        $collection = $this->_orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('order_type', $type);
        return count($collection); 
    }

    public function getOrderCollectionByType()
    {
      if(isset($_GET['otype'])) {
        $type = $_GET['otype'];
        $collection = $this->_orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('order_type', $type);
          //  foreach($collection as $order) {
          //   echo "<pre>";print_r($order->getData());die();
          // }
          $dataExport = $this->request->getParam('export_data');
          if(isset($dataExport)) {
              $this->csvexportHelper->exportAERportByTypeCsv($collection);
            }  
          
        return $collection;
      } else {
        return '';
      }
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
    
     
}