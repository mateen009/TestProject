<?php

namespace AscentDigital\Reports\Block\Adminhtml\Reports;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\ObjectManagerInterface;
use phpDocumentor\Reflection\Types\This;

class OrderInventoryReport extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'AscentDigital_Reports::order_inventory_report.phtml';

    protected $_objectManager = null;
    protected $itemCollectionFactory;
    protected $_storeManager;
    protected $_productCollectionFactory;
    protected $request;

    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        \Magento\Sales\Model\Order\ItemFactory $itemCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->request = $request;
        parent::__construct($context, $data);
    }

    public function getProductCollection() {
        $_storeId = 2; // firstnet
        $collection = $this->getItemCollection();
        $collection->addAttributeToSelect('product_id');
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToSelect('sku');
        $collection->getSelect()->join(
            array('order' => $collection->getTable('sales_order')),
            'main_table.order_id = order.entity_id and order.status in ("shipping", "complete", "processing")',
            array('customer_id')
        )
            ->columns(array('total_orders' => new \Zend_Db_Expr('COUNT(main_table.item_id)')))
            ->columns(array('total_qty' => new \Zend_Db_Expr('ROUND(SUM(main_table.qty_ordered))')))
            ->group('sku', 'sm_email');
        $collection->addAttributeToFilter('order.store_id', $_storeId);
        // echo "<pre>";print_r($collection->getData());die;
        return $collection;
    }

    public function getProducts()
    {
        $collection = $this->getProductCollection();   
        $email = $this->request->getParam('email');
        if (isset($email) && !empty($email)) {
            $collection->addAttributeToFilter('order.customer_email', array('like' => '%' . trim($email) . '%'));
        }
        $sm_email = $this->request->getParam('sm_email');
        if (isset($sm_email) && !empty($sm_email)) {
            $collection->addAttributeToFilter('order.sm_email', array('like' => '%' . trim($sm_email) . '%'));
        }
        $tm_email = $this->request->getParam('tm_email');
        if (isset($tm_email) && !empty($tm_email)) {
            $collection->addAttributeToFilter('order.tm_email', array('like' => '%' . trim($tm_email) . '%'));
        }
        $em_email = $this->request->getParam('em_email');
        if (isset($em_email) && !empty($em_email)) {
            $collection->addAttributeToFilter('order.em_email', array('like' => '%' . trim($em_email) . '%'));
        }
        // generate_pdf
        $generate_pdf = $this->request->getParam('generate_pdf');
        if (isset($generate_pdf)) {
            $this->generatePDF($collection);
        }
        
        return $collection;

        echo "<pre>";
        print_r($collection->getSelect()->__toString());
        die;
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

    public function getDue($sku)
    {
        $date = date('Y-m-d');
        $collection = $this->getItemCollection();
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToFilter('sku', $sku);
        $collection->addAttributeToFilter('rma_return_status', 'no');
        $collection->getSelect()->join(
            array('order' => $collection->getTable('sales_order')),
            'main_table.order_id = order.entity_id 
            and order.status = "shipping" 
            and order.due_date <= "' . $date . '" 
            and return_status in ("no", "partial")',
            array('entity_id')
        )
            ->columns(array('due' => new \Zend_Db_Expr('ROUND(SUM(main_table.qty_ordered))')))
            ->group('sku');
        $email = $this->request->getParam('email');
        if (isset($email) && !empty($email)) {
            $collection->addAttributeToFilter('order.customer_email', array('like' => '%' . trim($email) . '%'));
        }
        $sm_email = $this->request->getParam('sm_email');
        if (isset($sm_email) && !empty($sm_email)) {
            $collection->addAttributeToFilter('order.sm_email', array('like' => '%' . trim($sm_email) . '%'));
        }
        $tm_email = $this->request->getParam('tm_email');
        if (isset($tm_email) && !empty($em_email)) {
            $collection->addAttributeToFilter('order.em_email', array('like' => '%' . trim($em_email) . '%'));
        }
        $em_email = $this->request->getParam('em_email');
        if (isset($em_email) && !empty($em_email)) {
            $collection->addAttributeToFilter('order.em_email', array('like' => '%' . trim($em_email) . '%'));
        }
        $data = $collection->getData();
        if (count($data) > 0) {
            return $data[0]['due'];
        } else {
            return "0";
        }
    }

    public function getOnDemo($sku)
    {
        $collection = $this->getItemCollection();
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToFilter('sku', $sku);
        $collection->addAttributeToFilter('rma_return_status', 'no');
        $collection->getSelect()->join(
            array('order' => $collection->getTable('sales_order')),
            'main_table.order_id = order.entity_id 
            and order.status = "shipping" 
            and return_status in ("no", "partial")',
            array('entity_id')
        )
            ->columns(array('on_demo' => new \Zend_Db_Expr('ROUND(SUM(main_table.qty_ordered))')))
            ->group('sku');
        $email = $this->request->getParam('email');
        if (isset($email) && !empty($email)) {
            $collection->addAttributeToFilter('order.customer_email', array('like' => '%' . trim($email) . '%'));
        }
        $sm_email = $this->request->getParam('sm_email');
        if (isset($sm_email) && !empty($sm_email)) {
            $collection->addAttributeToFilter('order.sm_email', array('like' => '%' . trim($sm_email) . '%'));
        }
        $tm_email = $this->request->getParam('tm_email');
        if (isset($tm_email) && !empty($em_email)) {
            $collection->addAttributeToFilter('order.em_email', array('like' => '%' . trim($em_email) . '%'));
        }
        $em_email = $this->request->getParam('em_email');
        if (isset($em_email) && !empty($em_email)) {
            $collection->addAttributeToFilter('order.em_email', array('like' => '%' . trim($em_email) . '%'));
        }
        $data = $collection->getData();
        // print_r($data);die;
        if (count($data) > 0) {
            return $data[0]['on_demo'];
        } else {
            return "0";
        }
    }

    public function getReturned($sku)
    {
        $collection = $this->getItemCollection();
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToFilter('rma_return_status', 'yes');
        $collection->addAttributeToFilter('sku', $sku);
        $collection->getSelect()->join(
            array('order' => $collection->getTable('sales_order')),
            'main_table.order_id = order.entity_id 
            and order.status = "complete" 
            and return_status = "yes"',
            array('entity_id')
        )
            ->columns(array('returned' => new \Zend_Db_Expr('ROUND(SUM(main_table.qty_ordered))')))
            ->group('sku');
        $email = $this->request->getParam('email');
        if (isset($email) && !empty($email)) {
            $collection->addAttributeToFilter('order.customer_email', array('like' => '%' . trim($email) . '%'));
        }
        $sm_email = $this->request->getParam('sm_email');
        if (isset($sm_email) && !empty($sm_email)) {
            $collection->addAttributeToFilter('order.sm_email', array('like' => '%' . trim($sm_email) . '%'));
        }
        $tm_email = $this->request->getParam('tm_email');
        if (isset($tm_email) && !empty($em_email)) {
            $collection->addAttributeToFilter('order.em_email', array('like' => '%' . trim($em_email) . '%'));
        }
        $em_email = $this->request->getParam('em_email');
        if (isset($em_email) && !empty($em_email)) {
            $collection->addAttributeToFilter('order.em_email', array('like' => '%' . trim($em_email) . '%'));
        }
        $data = $collection->getData();
        if (count($data) > 0) {
            return $data[0]['returned'];
        } else {
            return "0";
        }
    }

    public function getItemCollection()
    {
        $collection = $this->itemCollectionFactory->create()->getCollection();
        $sku = $this->request->getParam('skus');
        if (isset($sku) && !empty($sku)) {
            $skuArr = explode(",", trim($sku));
            $collection->addAttributeToFilter('sku', array('in', $skuArr));
        }
        return $collection;
    }

    public function generatePDF($collection)
    {
    }

    public function generateCSV($collection)
    {
        // $name = date('m_d_Y_H_i_s');
        // $filepath = 'export/custom' . $name . '.csv';
        // $this->directory->create('export');
        // /* Open file */
        // $stream = $this->directory->openFile($filepath, 'w+');
        // $stream->lock();
        // $columns = $this->getColumnHeader();
        // foreach ($columns as $column) {
        //     $header[] = $column;
        // }

        // $stream->writeCsv($header);
        // foreach ($$collection as $item) {
        //     $orderData = [];
        //     $orderData[] = $item->getName();
        //     $orderData[] = $item->getTotalOrders();
        //     $orderData[] = $item->getTotalQty();
        //     $orderData[] = number_format($this->getOnDemo($item->getSku()));
        //     $orderData[] = number_format($this->getDue($item->getSku()));
        //     $orderData[] = number_format($this->getReturned($item->getSku()));
        //     $stream->writeCsv($orderData);
        // }

        // $content = [];
        // $content['type'] = 'filename'; // must keep filename
        // $content['value'] = $filepath;
        // $content['rm'] = '1'; //remove csv from var folder

        // $csvfilename = 'Order Inventory.csv';
        // return $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
    }

    /* Header Columns */
    public function getColumnHeader()
    {
        $headers = ['Item', 'Total Order', 'Total Quantity', 'On Demo', 'Due', 'Returned'];
        return $headers;
    }
}
