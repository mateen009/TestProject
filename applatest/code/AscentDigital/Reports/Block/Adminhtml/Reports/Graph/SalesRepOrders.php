<?php

namespace AscentDigital\Reports\Block\Adminhtml\Reports\Graph;


class SalesRepOrders extends \AscentDigital\Reports\Block\Adminhtml\Reports\SalesRepOrders
{

    /**
     * Sales Rep Orders Graph
     */
    public function salesRepOrders()
    {
        $collection = $this->_orderCollectionFactory->create()
            // ->addAttributeToSelect('entity_id')
            ->addAttributeToSelect('status')
            ->addFieldToFilter('store_id', '2')
            ->addFieldToFilter('status', array(array('shipping', 'processing', 'complete')))
        ->setOrder('customer_ts', 'DESC');
         $collection->getSelect()
         ->columns(array('orders' => new \Zend_Db_Expr('COUNT(entity_id)')))
         ->group('main_table.status');

         return $collection;
    }
    /**
     * Sales Rep Orderer Qty Graph
     */

    public function orderdQty(){
        $collection = $this->_orderCollectionFactory->create()
        ->addAttributeToSelect('status')
        ->addFieldToFilter('store_id', '2')
        ->addFieldToFilter('status', array(array('shipping','processing','complete')))
         ->setOrder('customer_ts', 'DESC');
         $collection->getSelect()
         ->columns(array('qty' => new \Zend_Db_Expr('ROUND(SUM(total_qty_ordered))')))
         ->group('main_table.status');
       
          return $collection;

    }
}
