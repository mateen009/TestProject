<?php

namespace AscentDigital\Reports\Block\Adminhtml\Reports\Graph;

use Magento\Backend\Block\Template\Context;

class TotalSalesRepOrdersReport extends \Magento\Backend\Block\Template
{
    protected $_customerFactory;

    public function __construct(
        Context $context,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        array $data = []
    ) {
        $this->_customerFactory = $customerFactory;
        parent::__construct($context, $data);
    }

    /**
     * totalSalesBySalesRepGraph
     */
    public function totalSalesBySalesRepGraph()
    {
        $_storeId = 2; // firstnet
        $collection = $this->_customerFactory->create()->getCollection();
        $collection->getSelect()->join(
            array('sales_order' => $collection->getTable('sales_order')),
            'e.entity_id = sales_order.customer_id and sales_order.status in ("shipping", "complete", "processing")',
            array('customer_email')
        )
            ->columns(array('total_orders' => new \Zend_Db_Expr('COUNT(sales_order.entity_id)')))
            ->columns(array('total_qty' => new \Zend_Db_Expr('SUM(sales_order.total_qty_ordered)')))
            ->group('sales_order.customer_id');
        $collection->addAttributeToFilter('Customer_Type', 1);
        $collection->addAttributeToFilter('website_id', 3);
        $collection->addAttributeToSelect('email');
        $collection->addAttributeToSelect('total_orders');
        $collection->addAttributeToSelect('total_qty');
        // echo '<pre>';
        // print_r(count($collection->getData()));
        // die('died');// $collection->addAttributeToSelect('total_qty');
        // die('here');
        // $collection->addAttributeToFilter('store_id',3);
        // die('erejdfksjdhfksjd');
        return $collection->getData();
    }
}
