<?php

namespace Magenest\RentalSystem\Block\Adminhtml\Sales;

class SalesReport extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected $_template = 'Magento_Reports::report/grid/container.phtml';
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magenest_RentalSystem';
        $this->_controller = 'adminhtml_sales_salesreport';
        $this->_headerText = __('Sales ProductReport');
        parent::_construct();
        $this->buttonList->remove('add');
    }

    /**
     * Get filter URL
     *
     * @return string
     */
    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);
        return $this->getUrl('*/*/salesreport', ['_current' => true]);
    }
}
