<?php

namespace Magenest\RentalSystem\Controller\Adminhtml\Report\Sales;

use Magenest\RentalSystem\Model\Flag;

class SalesReport extends \Magenest\RentalSystem\Controller\Adminhtml\SalesReport
{
    /**
     * execute the action
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->_showLastExecutionTime(Flag::REPORT_SALESREPORT_FLAG_CODE, 'salesreport');

        $this->_initAction()
            ->_setActiveMenu('Magenest_RentalSystem::salesreport')
            ->_addBreadcrumb(__('Sales ProductReport'), __('Sales ProductReport'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Sales ProductReport'));

        $gridBlock = $this->_view->getLayout()->getBlock('adminhtml_sales_salesreport.grid');
        $filterFormBlock = $this->_view->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction([$gridBlock, $filterFormBlock]);

        $this->_view->renderLayout();
    }
}
