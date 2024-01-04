<?php

namespace Magenest\RentalSystem\Block\Adminhtml\Sales;

class ProductReport extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected $_template = 'Magento_Reports::report/grid/container.phtml';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magenest_RentalSystem';
        $this->_controller = 'adminhtml_sales_productreport';
        $this->_headerText = __('Product ProductReport');
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
        return $this->getUrl('*/*/productreport', ['_current' => true]);
    }
}
