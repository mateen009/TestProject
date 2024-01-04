<?php

namespace AscentDigital\Reports\Block\Adminhtml\Reports;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\ObjectManagerInterface;

class SalesRepOrders extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'AscentDigital_Reports::sales_rep_orders.phtml';

    protected $_objectManager = null;
    protected $_orderCollectionFactory;
    protected $_storeManager;
    protected $_productCollectionFactory;
    protected $request;

    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->request = $request;
        parent::__construct($context, $data);
    }

    public function getOrders()
    {
        $_storeId = 2; // firstnet
        $collection = $this->getOrderCollection();
        $collection->getSelect()->group('order_item.order_id');

        // generate_pdf
        $generate_pdf = $this->request->getParam('generate_pdf');
        if (isset($generate_pdf)) {
            $this->generatePDF($collection);
        }

        return $collection;

        // echo "<pre>";
        // print_r($collection->getSelect()->__toString());
        // die;
    }

    public function getProductSkus($skus)
    {
        // print_r($email);die;
        $_storeId = $this->_storeManager->getStore()->getId();
        $_storeId = 2; //firstnet

        // $collection = $this->getItemCollection();
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*')
        ->addFieldToFilter('sku', array('in'=> $skus))
        ;
        $collection->addStoreFilter($_storeId);

        $productsData = array();

        foreach ($collection as $product) {
            $sku = $product->getSku();
            $pid = $product->getId();
            $pName = $product->getName();
            $productsData[$pid]['sku'] = $sku;
            $productsData[$pid]['name'] = $pName;
            //echo $pName." : ".$sku."<br/>";
        }

        // echo "<pre>";print_r($productsData);die();
        return $productsData;
    }



    public function getOrderCollection()
    {
        $collection = $this->_orderCollectionFactory->create()
            // ->addAttributeToSelect('entity_id')
            ->addAttributeToSelect('ns_so_number')
            ->addAttributeToSelect('customer_email')
            ->addAttributeToSelect('agency_name')
            ->addAttributeToSelect('customer_ts')
            ->addAttributeToSelect('status')
            ->addAttributeToSelect('return_status')
            ->addAttributeToSelect('sm_email')
            ->addAttributeToSelect('tm_email')
            ->addAttributeToSelect('em_email')
            ->addFieldToFilter('main_table.store_id', '2')
            ->addFieldToFilter('status', array('in' => array('processing', 'shipping', 'complete')))
            ->setOrder('customer_ts', 'DESC');
        $collection->getSelect()->join(
            array('order_item' => $collection->getTable('sales_order_item')),
            'main_table.entity_id = order_item.order_id',
            array('order_id', 'sku', 'name', 'ROUND(qty_ordered) as total_qty')
        );
        $sku = $this->request->getParam('skus');
        if (isset($sku) && !empty($sku)) {
            $skuArr = explode(",", trim($sku));
            $collection->addAttributeToFilter('sku', array('in', $skuArr));
        }
        $email = $this->request->getParam('email');
        if (isset($email) && !empty($email)) {
            $collection->addAttributeToFilter('main_table.customer_email', array('like' => '%' . trim($email) . '%'));
        }
        $sm_email = $this->request->getParam('sm_email');
        if (isset($sm_email) && !empty($sm_email)) {
            $collection->addAttributeToFilter('main_table.sm_email', array('like' => '%' . trim($sm_email) . '%'));
        }
        $tm_email = $this->request->getParam('tm_email');
        if (isset($tm_email) && !empty($tm_email)) {
            $collection->addAttributeToFilter('main_table.tm_email', array('like' => '%' . trim($tm_email) . '%'));
        }
        $em_email = $this->request->getParam('em_email');
        if (isset($em_email) && !empty($em_email)) {
            $collection->addAttributeToFilter('main_table.em_email', array('like' => '%' . trim($em_email) . '%'));
        }
        return $collection;
    }

    // public function generatePDF($collection)
    // {
    // }

}
