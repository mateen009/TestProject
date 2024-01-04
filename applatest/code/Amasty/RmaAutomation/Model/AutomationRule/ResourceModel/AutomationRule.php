<?php

namespace Amasty\RmaAutomation\Model\AutomationRule\ResourceModel;

use Amasty\RmaAutomation\Api\Data\AutomationRuleInterface;
use Magento\Rule\Model\ResourceModel\AbstractResource;

/**
 * Class AutomationRule
 */
class AutomationRule extends AbstractResource
{
    const TABLE_NAME = 'amasty_rma_automation_rules';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, AutomationRuleInterface::RULE_ID);
    }
}
