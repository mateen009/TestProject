<?php

namespace AscentDigital\Reports\Block\Reports\FirstNet;

class Dashboard extends \Magento\Framework\View\Element\Template
{
    protected $_orderCollectionFactory;
    protected $salesRep;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Sales\Model\Order\ItemFactory $itemCollectionFactory,
        \AscentDigital\Reports\Model\SalesReps $salesRep,
        \Mobility\QuoteRequest\Model\QuoteRequestFactory $quoteRequestFactory,
        \Magento\Customer\Model\Session $customerSession

    )
    {
        $this->_orderCollectionFactory = $orderCollectionFactory;

        $this->_customerFactory = $customerFactory;
        $this->_itemCollectionFactory = $itemCollectionFactory;
        $this->salesRep = $salesRep;
        $this->_quoteRequestFactory = $quoteRequestFactory;
        $this->_customerSession = $customerSession;
        parent::__construct($context);
    }




    //Demo Order collection
    public function getDemoOrderCollection()
    {
        $customerType = $this->_customerSession->getCustomerType();
        $collection = $this->_orderCollectionFactory->create()
            ->addFieldToSelect('*');
        $customerEmail = $this->_customerSession->getCustomer()->getEmail();
        if ($customerType == '3') {
            $collection->addAttributeToFilter('sm_email', array('like' => '%' . trim($customerEmail) . '%'));
        } elseif ($customerType == '4') {

            $collection->addAttributeToFilter('tm_email', array('like' => '%' . trim($customerEmail) . '%'));

        } elseif ($customerType == '5') {
            $collection->addAttributeToFilter('em_email', array('like' => '%' . trim($customerEmail) . '%'));

        }
        $collection->addFieldToFilter('status', 'shipping');
        $collection->getSelect()->limit(3);
        $collection->addFieldToFilter('store_id', 2);
        $collection->setOrder(
            'customer_ts',
            'desc'
        );

        return $collection;
    }
    //Due Back Order collection
    public function getDueBackOrderCollection()
    {
        $customerType = $this->_customerSession->getCustomerType();
        $collection = $this->_orderCollectionFactory->create()
            ->addFieldToSelect('*');
        $customerEmail = $this->_customerSession->getCustomer()->getEmail();
        if ($customerType == '3') {
            $collection->addAttributeToFilter('sm_email', array('like' => '%' . trim($customerEmail) . '%'));
        } elseif ($customerType == '4') {

            $collection->addAttributeToFilter('tm_email', array('like' => '%' . trim($customerEmail) . '%'));

        } elseif ($customerType == '5') {
            $collection->addAttributeToFilter('em_email', array('like' => '%' . trim($customerEmail) . '%'));

        }
        $collection->addFieldToFilter('status', 'shipping')
            ->addFieldToFilter('due_date', array('lteq' => date('Y-m-d')))
            ->addFieldToFilter('return_status', array('neq' => 'yes'));
        $collection->addFieldToFilter('store_id', 2);
        $collection->getSelect()->limit(3);
        $collection->setOrder(
            'customer_ts',
            'desc'
        );


        return $collection;
    }
    //Sales By Sales Rep Order  collection
    public function getSalesBySalesRepCollection()
    {
        $customerType = $this->_customerSession->getCustomerType();
        $collection = $this->_orderCollectionFactory->create()
            ->addFieldToSelect('*');
        $customerEmail = $this->_customerSession->getCustomer()->getEmail();
        if ($customerType == '3') {
            $collection->addAttributeToFilter('sm_email', array('like' => '%' . trim($customerEmail) . '%'));
        } elseif ($customerType == '4') {

            $collection->addAttributeToFilter('tm_email', array('like' => '%' . trim($customerEmail) . '%'));

        } elseif ($customerType == '5') {
            $collection->addAttributeToFilter('em_email', array('like' => '%' . trim($customerEmail) . '%'));

        }
        $collection->addFieldToFilter('status', array('in' => array('processing', 'shipping', 'complete')));
        $collection->addFieldToFilter('store_id', 2);
        $collection->setOrder(
            'customer_ts',
            'desc'
        );
        $collection->getSelect()->limit(3);
        return $collection;
    }
    //Total Sales By Sales Rep Order collection

    public function getTotalSalesBySalesRepCollection()
    {

        $collection = $this->_customerFactory->create()->getCollection();
        $customerId = $this->_customerSession->getCustomerId();
        $customerType = $this->_customerSession->getCustomerType();
        if ($customerType == 3) {
            $collection->addFieldToFilter('SalesManager_ID', $customerId);
        }
        if ($customerType == 4) {
            $collection->addFieldToFilter('TerritoryManager_ID', $customerId);
        }
        if ($customerType == 5) {
            $collection->addFieldToFilter('Executive_ID', $customerId);
        }

        $customerId = $this->_customerSession->getCustomerId();
        $collection->getSelect()->join(
            array('sales_order' => $collection->getTable('sales_order')),
            'e.entity_id = sales_order.customer_id and sales_order.status in ("shipping", "complete", "processing")',
            array('sm_email', 'tm_email', 'em_email')
        )
            ->columns(array('total_orders' => new \Zend_Db_Expr('COUNT(sales_order.entity_id)')))
            ->columns(array('total_qty' => new \Zend_Db_Expr('ROUND(SUM(sales_order.total_qty_ordered))')))
            ->group('sales_order.customer_id');
        $collection->getSelect()->limit(3);
        $collection->setOrder(
            'customer_ts',
            'desc'
        );


        return $collection;
    }
    //Order Inventory Report collection
    public function getOrderInventoryReportCollection()
    {
        $collection = $this->_itemCollectionFactory->create()->getCollection();
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToSelect('sku');
        $collection->getSelect()->join(
            array('order' => $collection->getTable('sales_order')),
            'main_table.order_id = order.entity_id and order.status in ("shipping", "complete", "processing")',
            array('customer_id')
        )
            ->columns(array('total_orders' => new \Zend_Db_Expr('COUNT(main_table.item_id)')))
            ->columns(array('total_qty' => new \Zend_Db_Expr('ROUND(SUM(main_table.qty_ordered))')))
            ->group('sku');
        $collection->addAttributeToFilter('order.store_id', '2');
        $collection->getSelect()->limit(3);
        $customerType = $this->_customerSession->getCustomerType();
        $customerEmail = $this->_customerSession->getCustomer()->getEmail();
        if ($customerType == '3') {
            $collection->addAttributeToFilter('order.sm_email', array('like' => '%' . trim($customerEmail) . '%'));
        } elseif ($customerType == '4') {

            $collection->addAttributeToFilter('order.tm_email', array('like' => '%' . trim($customerEmail) . '%'));



        } elseif ($customerType == '5') {
            $collection->addAttributeToFilter('order.em_email', array('like' => '%' . trim($customerEmail) . '%'));

        }

        return $collection;
    }
    //My Quote Requests collection
    public function getQuoteRequestCollection()
    {
        $collection = $this->_quoteRequestFactory->create()->getCollection();
        $customerType = $this->_customerSession->getCustomerType();
        $customerId = $this->_customerSession->getCustomerId();


        if ($customerType == '3') {
            $collection->addFieldToFilter('approval_1_id', $customerId);
        } else if ($customerType == '4') {
            $collection->addFieldToFilter('approval_2_id', $customerId);
        } else if ($customerType == '5') {
            $collection->addFieldToFilter('approval_3_id', $customerId);
        }
        $collection->getSelect()->limit(3);
        $collection->setOrder(
            'id',
            'desc'
        );


       

        return $collection;
    }

    //Manage salesrep,sales managers and territory managers collection
    public function getManageSalesRepCollection()
    {
        $collection = $this->_customerFactory->create()->getCollection();
        $collection->addAttributeToSelect('*');
      
        $customerType = $this->_customerSession->getCustomerType();
        $customerId = $this->_customerSession->getCustomerId();
        if ($customerType == 3) {
            $collection->addFieldToFilter('Customer_Type', '1');
            $collection->addFieldToFilter('SalesManager_ID', $customerId);
        } elseif ($customerType == 4) {
            $collection->addFieldToFilter('Customer_Type', '3')
                ->addFieldToFilter('TerritoryManager_ID', $customerId);
        } elseif ($customerType == 5) {
            $collection->addFieldToFilter('Customer_Type', '4')
                ->addFieldToFilter('Executive_ID', $customerId);
        }
        $collection->setOrder(
            'id',
            'desc'
        );
        $collection->getSelect()->limit(3);
            
        return $collection;
    }
    public function getCustomerType()
    {
        return $this->_customerSession->getCustomerType();
    }
    //Orders Approval Collection
    public function getOrdersApprovalsCollection()
    {
        $collection = $this->_orderCollectionFactory->create();
        $customerType = $this->_customerSession->getCustomerType();
        $customerId = $this->_customerSession->getCustomerId();
        if ($customerType == 3) {
            $collection->addFieldToFilter('approval_1_id', $customerId);
        } elseif ($customerType == 4) {
            $collection->addFieldToFilter('approval_2_id', $customerId);
        } elseif ($customerType == 5) {
            $collection->addFieldToFilter('approval_3_id', $customerId);
        }
        $collection->addFieldToFilter('store_id', 2);

        $collection->getSelect()->limit(3);
        $collection->setOrder(
            'customer_ts',
            'desc'
        );

        return $collection;
    }
}