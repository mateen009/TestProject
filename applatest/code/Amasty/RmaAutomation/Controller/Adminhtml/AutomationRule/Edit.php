<?php

namespace Amasty\RmaAutomation\Controller\Adminhtml\AutomationRule;

use Amasty\RmaAutomation\Api\AutomationRuleRepositoryInterface;
use Amasty\RmaAutomation\Api\Data\AutomationRuleInterface;
use Amasty\RmaAutomation\Controller\Adminhtml\AbstractAutomationRule;
use Amasty\RmaAutomation\Model\AutomationRule\AutomationRule;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;

/**
 * Class Edit
 */
class Edit extends AbstractAutomationRule
{
    /**
     * @var AutomationRuleRepositoryInterface
     */
    private $repository;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        Action\Context $context,
        AutomationRuleRepositoryInterface $repository,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->registry = $registry;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $title = __('New Return Rule');

        if ($ruleId = (int)$this->getRequest()->getParam(AutomationRuleInterface::RULE_ID)) {
            try {
                $model = $this->repository->getById($ruleId);
                $title = __('Edit Automation Rule %1', $model->getName());
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This rule no longer exists.'));

                return $this->_redirect('*/*/index');
            }
        } else {
            $model = $this->repository->getEmptyRuleModel();
        }
        $model->getConditions()->setJsFormObject('rule_conditions_fieldset');
        $this->registry->register(AutomationRule::CURRENT_RETURN_RULE, $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_Rma::return_rules');
        $resultPage->addBreadcrumb(__('Return Rules'), __('Return Rules'));
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
