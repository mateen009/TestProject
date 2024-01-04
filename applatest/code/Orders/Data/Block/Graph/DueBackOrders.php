<?php

namespace Orders\Data\Block\Graph;

class DueBackOrders extends \Magento\Framework\View\Element\Template
{

    protected $_orderCollectionFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Session $customerSession
    )
    {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_customerFactory = $customerFactory;
        $this->_customerSession = $customerSession;
        parent::__construct($context);
    }
    //function due back order of current date
    public function dueBackCurrentDate(){

        $collection = $this->getCollection();
        $collection->addAttributeToSelect('due_date')
            ->addFieldToFilter('due_date', array('eq' => date('Y-m-d')));
         $collection->getSelect()
            ->columns(array('orders' => new \Zend_Db_Expr('COUNT(entity_id)')));
        return $collection->getData()['0'];
    }
    //function due back order of late date
    public function dueBackLateOrders(){
        $collection = $this->getCollection();
        $collection->addAttributeToSelect('due_date')
            ->addFieldToFilter('due_date', array('lt' => date('Y-m-d')));
            $collection->getSelect()
            ->columns(array('orders' => new \Zend_Db_Expr('COUNT(entity_id)')));
        return $collection->getData()['0'];
    }
    //function due back order of partial status
    public function returnStatusPartial()
    {
        $collection = $this->getCollection();
        $collection->addFieldToFilter('return_status', 'partial')
            ->addAttributeToSelect('return_status');
        $collection->getSelect()
            ->columns(array('orders' => new \Zend_Db_Expr('COUNT(entity_id)')));
        return $collection->getData()['0']; 
    }
    //function due back order of status no
    public function returnStatusNo()
    {
        $collection = $this->getCollection();
        $collection->addFieldToFilter('return_status', 'no')
            ->addAttributeToSelect('return_status');
        $collection->getSelect()
            ->columns(array('orders' => new \Zend_Db_Expr('COUNT(entity_id)')));
        return $collection->getData()['0']; 
    }
    public function getCollection(){
        $customerType = $this->_customerSession->getCustomerType();
        $customerEmail = $this->_customerSession->getCustomer()->getEmail();
        $collection = $this->_orderCollectionFactory->create();
        $collection->addFieldToFilter('main_table.store_id', '2')
        ->addFieldToFilter('due_date', array('lteq' => date('Y-m-d')))
        ->addFieldToFilter('status', 'shipping');
        if ($customerType == '1') {
            $collection->addAttributeToFilter('customer_email', trim($customerEmail));
        }
        elseif ($customerType == '3') {
            $collection->addAttributeToFilter('sm_email',trim($customerEmail));
        } elseif ($customerType == '4') {

            $collection->addAttributeToFilter('tm_email', trim($customerEmail));

        } elseif ($customerType == '5') {
            $collection->addAttributeToFilter('em_email', trim($customerEmail));

        }
        return $collection;
    }
}