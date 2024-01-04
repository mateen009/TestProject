<?php

namespace AscentDigital\Reports\Block\Adminhtml\Grid\Renderer\OrderReportByInventroy;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class Returned extends AbstractRenderer
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
        $collection->addAttributeToFilter('rma_return_status', 'yes');
        $collection->addAttributeToFilter('sku', trim($row->getSku()));
        $collection->getSelect()->join(
            array('order' => $collection->getTable('sales_order')),
            'main_table.order_id = order.entity_id 
            and order.status = "complete" 
            and return_status = "yes"',
            array('entity_id')
        )
            ->columns(array('returned' => new \Zend_Db_Expr('ROUND(SUM(main_table.qty_ordered))')))
            ->group('sku');
        $data = $collection->getData();
        if (count($data) > 0) {
            return $data[0]['returned'];
        } else {
            return "0";
        }
    }
}
