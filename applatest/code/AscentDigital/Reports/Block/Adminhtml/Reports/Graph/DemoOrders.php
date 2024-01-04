<?php

namespace AscentDigital\Reports\Block\Adminhtml\Reports\Graph;

use Magento\Backend\Block\Template\Context;

class DemoOrders extends \AscentDigital\Reports\Block\Adminhtml\Reports\DemoOrders
{

    /**
     * Demo Orders Graph
     */
    public function demoOrders()
    {
        $collection = $this->_orderCollectionFactory->create()
        ->addAttributeToSelect('entity_id')
        ->addAttributeToSelect('status')
        ->addAttributeToSelect('total_qty_ordered')
        ->addFieldToFilter('store_id', '2')
        ->addFieldToFilter('status', 'shipping')
        ->setOrder('customer_ts', 'DESC');
        $collection->getSelect()
        ->columns(array('orders' => new \Zend_Db_Expr('COUNT(entity_id)')))
        ->columns(array('qty' => new \Zend_Db_Expr('ROUND(SUM(total_qty_ordered))')))
        ->group('main_table.status');
        return $collection->getData();
    }
}
