<?php

namespace AscentDigital\Reports\Block\Adminhtml\Reports\Graph;

class DueBackOrders extends \AscentDigital\Reports\Block\Adminhtml\Reports\DueOrders
{
    //collection due back order of current date
    public function dueBackCurrentDate(){
        $collection = $this->_orderCollectionFactory->create()
            ->addAttributeToSelect('entity_id')
            ->addAttributeToSelect('due_date')
            ->addFieldToFilter('main_table.store_id', '2')
            ->addFieldToFilter('status', 'shipping')
            ->addFieldToFilter('due_date', array('eq' => date('Y-m-d')));
        $collection->getSelect()
            ->columns(array('orders' => new \Zend_Db_Expr('COUNT(entity_id)')));
        return $collection->getData()['0'];
    }
    //collection due back order of late date
    public function dueBackLateOrders(){
        $collection = $this->_orderCollectionFactory->create()
            ->addAttributeToSelect('due_date')
            ->addFieldToFilter('main_table.store_id', '2')
            ->addFieldToFilter('status', 'shipping')
            ->addFieldToFilter('due_date', array('lt' => date('Y-m-d')));
        $collection->getSelect()
            ->columns(array('orders' => new \Zend_Db_Expr('COUNT(entity_id)')));
       
        return $collection->getData()['0'];
    }
    //collection due back order of partial status
    public function returnStatusPartial()
    {
        $collection = $this->_orderCollectionFactory->create();
        $collection->addFieldToFilter('return_status', 'partial')
            ->addAttributeToSelect('return_status')
            ->addFieldToFilter('main_table.store_id', '2')
            ->addFieldToFilter('due_date', array('lteq' => date('Y-m-d')))
            ->addFieldToFilter('status', 'shipping');
        $collection->getSelect()
            ->columns(array('orders' => new \Zend_Db_Expr('COUNT(entity_id)')));
        
        return $collection->getData()['0']; 
    }
    //collection due back order of status no
    public function returnStatusNo()
    {
        $collection = $this->_orderCollectionFactory->create();
        $collection->addFieldToFilter('return_status', 'no')
            ->addAttributeToSelect('return_status')
            ->addFieldToFilter('main_table.store_id', '2')
            ->addFieldToFilter('due_date', array('lteq' => date('Y-m-d')))
            ->addFieldToFilter('status', 'shipping');
        $collection->getSelect()
            ->columns(array('orders' => new \Zend_Db_Expr('COUNT(entity_id)')));
        
        return $collection->getData()['0']; 
    }
}