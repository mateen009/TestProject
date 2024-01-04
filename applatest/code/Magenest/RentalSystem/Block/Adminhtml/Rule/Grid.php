<?php
namespace Magenest\RentalSystem\Block\Adminhtml\Rule;

class Grid extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magenest_RentalSystem';
        $this->_controller = 'adminhtml_rental_rule';
        $this->_headerText = __('Rental Rule');
        $this->_addButtonLabel = __('Add New Rule');
        parent::_construct();
    }
}
