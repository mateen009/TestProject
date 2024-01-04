<?php

namespace Magenest\RentalSystem\Controller\Adminhtml\Report\Sales;

class ProductReport extends \Magenest\RentalSystem\Controller\Adminhtml\ProductReport
{
    /**
     * execute the action
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->_initAction()
            ->_setActiveMenu('Magenest_RentalSystem::productreport')
            ->_addBreadcrumb(__('Product ProductReport'), __('Product ProductReport'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Product ProductReport'));

        $this->_view->renderLayout();
    }
}
