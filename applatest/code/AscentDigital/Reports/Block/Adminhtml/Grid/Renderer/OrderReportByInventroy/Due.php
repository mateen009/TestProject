<?php

namespace AscentDigital\Reports\Block\Adminhtml\Grid\Renderer\OrderReportByInventroy;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class Due extends AbstractRenderer
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
        $date = date('Y-m-d');
        $collection = $this->itemCollectionFactory->create()->getCollection();
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToFilter('sku', trim($row->getSku()));
        $collection->addAttributeToFilter('rma_return_status', 'no');
        $collection->getSelect()->join(
            array('order' => $collection->getTable('sales_order')),
            'main_table.order_id = order.entity_id 
            and order.status = "shipping" 
            and order.due_date <= "'.$date.'" 
            and return_status in ("no", "partial")',
            array('entity_id')
        )
        ->columns(array('due' => new \Zend_Db_Expr('ROUND(SUM(main_table.qty_ordered))')))
        ->group('sku');
        $data = $collection->getData();
        if (count($data) > 0) {
            return $data[0]['due'];
        } else {
            return "0";
        }
    }
}
