<?php

namespace AscentDigital\Reports\Block\Adminhtml\Grid\Renderer\OrderReportByInventroy;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Backend\Block\Widget\Grid\Column\Filter\AbstractFilter;
use Magento\Framework\DataObject;

class OnDemo extends AbstractRenderer
{
    protected $itemCollectionFactory;

    public function __construct(
        Context $context,
        \Magento\Sales\Model\Order\ItemFactory $itemCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_authorization = $context->getAuthorization();
        $this->itemCollectionFactory = $itemCollectionFactory;
    }
    
    public function render(DataObject $row)
    {
        $collection = $this->itemCollectionFactory->create()->getCollection();
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToFilter('sku', trim($row->getSku()));
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
        $data = $collection->getData();
        // print_r($data);die;
        if (count($data) > 0) {
            return $data[0]['on_demo'];
        } else {
            return "0";
        }
    }
}
