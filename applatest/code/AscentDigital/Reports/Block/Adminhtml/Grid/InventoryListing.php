<?php

namespace AscentDigital\Reports\Block\Adminhtml\Grid;

use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\ObjectManagerInterface;

class InventoryListing extends Extended
{
    protected $registry;
    protected $_objectManager = null;
    protected $_productCollectionFactory;

    public function __construct(
        Context $context,
        Data $backendHelper,
        Registry $registry,
        ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->registry = $registry;
        $this->_productCollectionFactory = $productCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }
    protected function _construct()
    {
        parent::_construct();
        $this->setId('inventory_listing');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->addExportType('mobilecg/export/inventorylisting', __('CSV'));

    }

    /**
     * add Column Filter To Collection
     */
    protected function _addColumnFilterToCollection($column)
    {
        parent::_addColumnFilterToCollection($column);
        return $this;
    }

    /**
     * prepare collection
     */
    protected function _prepareCollection()
    {
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
        // echo '<pre>';print_r($collection->getSelect()->__toString());die;
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        /* @var $model \Webspeaks\ProductsGrid\Model\Slide */

        $this->addColumn(
            'entity_id',
            [
                'header' => __('Product ID'),
                'type' => 'number',
                'index' => 'entity_id',
                'header_css_class' => 'entity_id',
                'column_css_class' => 'entity_id',
            ]
        );

        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
                'class' => 'name',
                'width' => '50px',
            ]
        );
        $this->addColumn(
            'sku',
            [
                'header' => __('Sku'),
                'index' => 'sku',
                'class' => 'sku',
                'width' => '50px',
                'filter_condition_callback' => [$this, '_skuFilterCollection'],
            ]
        );

        $this->addColumn(
            'qty',
            [
                'header' => __('Stock'),
                'type' => 'number',
                'index' => 'qty',
                'width' => '50px',
                'filter' => false,
                'values' => 'sdfa'
            ]
        );

        $this->addColumn(
            'reserve_qty',
            [
                'header' => __('Saleable Stock'),
                'type' => 'number',
                'index' => 'reserve_qty',
                'filter' => false,
                'width' => '50px',
                'filter_condition_callback' => [$this, '_filterCollection'],
                'renderer' => 'AscentDigital\Reports\Block\Adminhtml\Grid\Renderer\SaleableQty'
            ]
        );


        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('mobilecg/grid/inventorylisting', ['_current' => true]);
    }

    /**
     * @param  object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return '';
    }

    protected function _filterCollection($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        // echo "<pre>";
        // print_r($value);
        $this->getCollection()->getSelect()->columns(['saleable_stock' => new \Zend_Db_Expr('stock_item.qty + reseveation.quantity')])->where('(stock_item.qty + reseveation.quantity) between '.$value['from'].' and '.$value['to']);
        // print_r($this->getCollection()->getSelect()->__toString());die;
        // $this->getCollection()->getSelect()->where(
        //     // do filter 
        // );
        return $this;
    }

    protected function _skuFilterCollection($collection, $column)
    {
        $skus = trim($column->getFilter()->getValue());
        if (isset($skus) && !empty($skus)) {
            $skuArray = explode(",", $skus);     // explode skus by coma 
            $this->getCollection()->addAttributeToFilter('sku', array('in', $skuArray));
        }
        return $this;
    }
}
