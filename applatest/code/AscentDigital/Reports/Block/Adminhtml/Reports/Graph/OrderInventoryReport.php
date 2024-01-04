<?php

namespace AscentDigital\Reports\Block\Adminhtml\Reports\Graph;

class OrderInventoryReport extends \AscentDigital\Reports\Block\Adminhtml\Reports\OrderInventoryReport
{

    //order inventory function for collecting total orders and total quantity orders
    public function orderInventoryReport()
    {
        $collection = $this->getProductCollection();
        return $collection;
    }
    //order inventory function for collecting On demo orders
    public function onDemo()
    {
        $collection = $this->getItemCollection();
       // $collection->addAttributeToSelect('name');
        $collection->addAttributeToSelect('sku');
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
          
        
        return $collection;
    }
    //order inventory function for collecting Due orders
    public function due(){
        $date = date('Y-m-d');
        $collection = $this->getItemCollection();
        $collection->addAttributeToSelect('sku');
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
        
        return $collection;
    } 

    //order inventory function for collecting Returned orders
    public function returned(){
        $collection = $this->getItemCollection();
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToFilter('rma_return_status', 'yes');
        $collection->getSelect()->join(
            array('order' => $collection->getTable('sales_order')),
            'main_table.order_id = order.entity_id 
            and order.status = "complete" 
            and return_status = "yes"',
            array('entity_id')
        )
            ->columns(array('returned' => new \Zend_Db_Expr('ROUND(SUM(main_table.qty_ordered))')))
            ->group('sku');
           
        return $collection;
    }
}
