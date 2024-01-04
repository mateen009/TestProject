<?php

namespace AscentDigital\Reports\Block\Reports\FirstNet\Graph;

class DemoOrders extends \Magento\Framework\View\Element\Template
{

    protected $_orderCollectionFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Model\Session $customerSession
        )
	{
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_customerSession = $customerSession;
		parent::__construct($context);
	}
    /**
     * Demo Orders Graph
     */
    public function demoOrders()
    {
        $customerType = $this->_customerSession->getCustomerType();
        $collection = $this->_orderCollectionFactory->create()
        ->addAttributeToSelect('entity_id')
        ->addAttributeToSelect('status')
        ->addAttributeToSelect('total_qty_ordered')
        ->addFieldToFilter('store_id', '2')
        ->addFieldToFilter('status', 'shipping')
        ->setOrder('customer_ts', 'DESC');
        $customerEmail = $this->_customerSession->getCustomer()->getEmail();
           
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
        $collection->getSelect()
        ->columns(array('orders' => new \Zend_Db_Expr('COUNT(entity_id)')))
        ->columns(array('qty' => new \Zend_Db_Expr('ROUND(SUM(total_qty_ordered))')))
        ->group('main_table.status');
        return $collection->getData();
    }
}
