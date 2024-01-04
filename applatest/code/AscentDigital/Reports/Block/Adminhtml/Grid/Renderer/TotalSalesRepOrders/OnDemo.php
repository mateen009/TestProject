<?php

namespace AscentDigital\Reports\Block\Adminhtml\Grid\Renderer\TotalSalesRepOrders;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class OnDemo extends AbstractRenderer
{
    protected $_orderCollectionFactory;
    
    public function __construct(
        Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_authorization = $context->getAuthorization();
        $this->_orderCollectionFactory = $orderCollectionFactory;
    }

    public function render(DataObject $row)
    {
        
        $collection = $this->_orderCollectionFactory->create();
        $collection->addAttributeToSelect('entity_id');
        $collection->addAttributeToFilter('customer_id', $row->getId());
        $collection->addAttributeToFilter('status', 'shipping');
        $collection->addAttributeToFilter('return_status', array('in','no','partial'));
        if ($collection->count() > 0){
            return $collection->count();
        }
        return '0';
    }
}
