<?php

namespace Amasty\RmaAutomation\Controller\Adminhtml\AutomationRule;

use Amasty\RmaAutomation\Api\Data\AutomationRuleInterfaceFactory;
use Amasty\RmaAutomation\Controller\Adminhtml\AbstractAutomationRule;
use Magento\Backend\App\Action;
use Magento\Rule\Model\Condition\AbstractCondition;

/**
 * Class NewConditionHtml
 */
class NewConditionHtml extends AbstractAutomationRule
{
    /**
     * @var AutomationRuleInterfaceFactory
     */
    private $ruleFactory;

    public function __construct(Action\Context $context, AutomationRuleInterfaceFactory $ruleFactory)
    {
        parent::__construct($context);
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * Generate Condition HTML form. Ajax
     */
    public function execute()
    {
        //for condition id in formats 1--1, not format to int
        $conditionId = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getPost('type')));
        $type = $typeArr[0];

        if (empty($type)) {
            return;
        }
        $model = $this->_objectManager->create($type)
            ->setId($conditionId)
            ->setType($type)
            ->setRule($this->ruleFactory->create())
            ->setPrefix('conditions');

        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $model->setFormName($this->getRequest()->getParam('form_namespace'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }
}
