<?php

namespace AscentDigital\Reports\Block\Adminhtml\Grid;

use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\ObjectManagerInterface;

class OrderReportByInventory extends Extended
{
    protected $_objectManager = null;
    protected $itemCollectionFactory;

    public function __construct(
        Context $context,
        Data $backendHelper,
        ObjectManagerInterface $objectManager,
        \Magento\Sales\Model\Order\ItemFactory $itemCollectionFactory,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->itemCollectionFactory = $itemCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }
    protected function _construct()
    {
        parent::_construct();
        $this->setId('order_report_by_inventory');
        $this->setDefaultSort('product_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
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
        $collection = $this->itemCollectionFactory->create()->getCollection();
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
            'product_id',
            [
                'header' => __('Product ID'),
                'type' => 'number',
                'index' => 'product_id',
                'header_css_class' => 'product_id',
                'column_css_class' => 'product_id',
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
            'total_orders',
            [
                'header' => __('Total Orders'),
                'filter' => false,
                'index' => 'total_orders',
                'width' => '50px',
                'values' => 'sdfa',
                'filter_condition_callback' => [$this, '_filterCollection'],
                ]
        );

        $this->addColumn(
            'total_qty',
            [
                'header' => __('Total Qty'),
                'filter' => false,
                'index' => 'total_qty',
                'width' => '50px',
                'values' => 'sdfa',
                'filter_condition_callback' => [$this, '_filterCollection'],
                ]
        );

        // $this->addColumn(
        //     'on_demo',
        //     [
        //         'header' => __('On Demo'),
        //         'width' => '50px',
        //         // 'filter' => false,
        //         'filter_condition_callback' => [$this, '_filterCollection'],
        //         'renderer' => 'AscentDigital\Reports\Block\Adminhtml\Grid\Renderer\OrderReportByInventroy\OnDemo'
        //     ]
        // );

        // $this->addColumn(
        //     'due',
        //     [
        //         'header' => __('Due'),
        //         'width' => '50px',
        //         'filter' => false,
        //         'renderer' => 'AscentDigital\Reports\Block\Adminhtml\Grid\Renderer\OrderReportByInventroy\Due'
        //     ]
        // );

        // $this->addColumn(
        //     'returned',
        //     [
        //         'header' => __('Returned'),
        //         'width' => '50px',
        //         'filter' => false,
        //         'renderer' => 'AscentDigital\Reports\Block\Adminhtml\Grid\Renderer\OrderReportByInventroy\Returned'
        //     ]
        // );

        $this->addColumn(
            'sm_email',
            [
                'header' => __('SM Email'),
                'width' => '50px',
                'filter_condition_callback' => [$this, '_SMFilterCollection'],
                // 'renderer' => 'AscentDigital\Reports\Block\Adminhtml\Grid\Renderer\OrderReportByInventroy\Returned'
            ]
        );

        $this->addColumn(
            'tm_email',
            [
                'header' => __('TM Email'),
                'width' => '50px',
                'filter_condition_callback' => [$this, '_TMFilterCollection'],
                // 'renderer' => 'AscentDigital\Reports\Block\Adminhtml\Grid\Renderer\OrderReportByInventroy\Returned'
            ]
        );

        $this->addColumn(
            'em_email',
            [
                'header' => __('EM Email'),
                'width' => '50px',
                'filter_condition_callback' => [$this, '_EMFilterCollection'],
                // 'renderer' => 'AscentDigital\Reports\Block\Adminhtml\Grid\Renderer\OrderReportByInventroy\Returned'
            ]
        );

        // $this->addColumn(
        //     'quantity',
        //     [
        //         'header' => __('Saleable Stock'),
        //         'type' => 'number',
        //         'index' => 'quantity',
        //         'width' => '50px',
        //         'filter_condition_callback' => [$this, '_filterCollection'],
        //         'renderer' => 'AscentDigital\Reports\Block\Adminhtml\Grid\Renderer\SaleableQty'
        //     ]
        // );


        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('mobilecg/grid/orderreportbyinventory', ['_current' => true]);
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
        // print_r($this->getCollection()->getData());die;
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

    protected function _SMFilterCollection($collection, $column)
    {
        $value = trim($column->getFilter()->getValue());
        $collection->addAttributeToFilter('sm_email', array('like' => '%'.$value.'%'));
        return $this;
    }

    protected function _TMFilterCollection($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        $value = trim($column->getFilter()->getValue());
        $collection->addAttributeToFilter('tm_email', array('like' => '%'.$value.'%'));
        return $this;
    }

    protected function _EMFilterCollection($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        $value = trim($column->getFilter()->getValue());
        $collection->addAttributeToFilter('em_email', array('like' => '%'.$value.'%'));
        return $this;
    }
}
