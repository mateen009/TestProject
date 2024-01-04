<?php

namespace AscentDigital\Reports\Block\Adminhtml\Reports;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\ObjectManagerInterface;
use phpDocumentor\Reflection\Types\This;

class OrderInventoryGraph extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'AscentDigital_Reports::orderinventorygraph.phtml';

    protected $_objectManager = null;
    protected $itemCollectionFactory;
    protected $_storeManager;
    protected $_productCollectionFactory;
    protected $request;
    protected $exportHelper;
    protected $_orderCollectionFactory;
    
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        \Magento\Sales\Model\Order\ItemFactory $itemCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \AscentDigital\Reports\Helper\ExportOrderInventory $exportHelper,
         \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->exportHelper = $exportHelper;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->request = $request;
        parent::__construct($context, $data);
    }

    public function getProductCollection() {
       $_storeId = 2; // firstnet
        $collection = $this->_productCollectionFactory->create()->addAttributeToSelect('entity_id');
        $collection->getSelect()->join(
            array('stock_item' => $collection->getTable('cataloginventory_stock_item')),
            'e.entity_id = stock_item.product_id',
            array('qty')
        );
        $collection->getSelect()->joinLeft(
            array('reseveation' => $collection->getTable('inventory_reservation')),
            'e.sku = reseveation.sku',
            array('reseveation.quantity')
        )
        ->columns(array('reserve_qty' => new \Zend_Db_Expr('SUM(reseveation.quantity)')))
        ->group('e.sku');
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('qty');
        $collection->addAttributeToSelect('reserve_qty');
        $collection->addAttributeToFilter('status', 1);
        $collection->addStoreFilter($_storeId);
        return $collection;
    }

    public function getQty($skus)
    {
        $qty=0.00;
         $_storeId = 2; // firstnet
         $collection = $this->_productCollectionFactory->create()->addAttributeToSelect('entity_id');
         $collection->getSelect()->join(
             array('stock_item' => $collection->getTable('cataloginventory_stock_item')),
             'e.entity_id = stock_item.product_id',
             array('qty')
         );
         $collection->getSelect()->joinLeft(
             array('reseveation' => $collection->getTable('inventory_reservation')),
             'e.sku = reseveation.sku',
             array('reseveation.quantity')
         )
         ->columns(array('reserve_qty' => new \Zend_Db_Expr('SUM(reseveation.quantity)')))
         ->group('e.sku');
         $collection->addAttributeToSelect('qty');
         $collection->addAttributeToSelect('reserve_qty');
         $collection->addAttributeToFilter('status', 1);
         $collection->addFieldToFilter('sku', array(array('regexp' => $skus)));
         $collection->addStoreFilter($_storeId);
        foreach($collection as $data){
            $qty=$data['qty'];
        }
         return $qty;
    }
    
    public function getSaleableQty($skus)
    {
        $qty=0.00;
         $_storeId = 2; // firstnet
         $collection = $this->_productCollectionFactory->create()->addAttributeToSelect('entity_id');
         $collection->getSelect()->join(
             array('stock_item' => $collection->getTable('cataloginventory_stock_item')),
             'e.entity_id = stock_item.product_id',
             array('qty')
         );
         $collection->getSelect()->joinLeft(
             array('reseveation' => $collection->getTable('inventory_reservation')),
             'e.sku = reseveation.sku',
             array('reseveation.quantity')
         )
         ->columns(array('reserve_qty' => new \Zend_Db_Expr('SUM(reseveation.quantity)')))
         ->group('e.sku');
         $collection->addAttributeToSelect('qty');
         $collection->addAttributeToSelect('reserve_qty');
         $collection->addAttributeToFilter('status', 1);
         $collection->addFieldToFilter('sku', array(array('regexp' => $skus)));
         $collection->addStoreFilter($_storeId);
        foreach($collection as $data){
            $qty=$data['qty']+$data['reserve_qty'];
        }
         return $qty;
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
    private function getOrderCollectionFactory()
    {
        if ($this->_orderCollectionFactory === null) {
            $this->_orderCollectionFactory = ObjectManager::getInstance()->get(CollectionFactoryInterface::class);
        }
        return $this->_orderCollectionFactory;
    }
    /* Header Columns */
    public function getColumnHeader()
    {
        $headers = ['Item', 'Total Order', 'Total Quantity', 'On Demo', 'Due', 'Returned'];
        return $headers;
    }
}


