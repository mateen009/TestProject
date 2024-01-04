<?php
namespace Cminds\Oapm\Block\Adminhtml\Sales;

class Order extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_sales_order';
        $this->_blockGroup = 'Cminds_Oapm';
        $this->_headerText = __(\Cminds\Oapm\Controller\Adminhtml\Oapm\Index::TITLE);
        parent::_construct();
        $this->removeButton('add');
    }
}
