<?php

namespace Amasty\RmaAutomation\Controller\Adminhtml\AutomationRule;

use Amasty\RmaAutomation\Controller\Adminhtml\AbstractAutomationRule;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Index
 */
class Index extends AbstractAutomationRule
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_RmaAutomation::automation_rules');
        $resultPage->addBreadcrumb(__('RMA Automation Rules'), __('RMA Automation Rules'));
        $resultPage->addBreadcrumb(__('Automation Rules'), __('Automation Rules'));
        $resultPage->getConfig()->getTitle()->prepend(__('Automation Rules'));

        return $resultPage;
    }
}
