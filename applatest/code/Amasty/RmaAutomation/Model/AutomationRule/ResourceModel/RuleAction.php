<?php

namespace Amasty\RmaAutomation\Model\AutomationRule\ResourceModel;

use Amasty\RmaAutomation\Api\Data\RuleActionInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class RuleAction
 */
class RuleAction extends AbstractDb
{
    const TABLE_NAME = 'amasty_rma_automation_rule_action';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, RuleActionInterface::ACTION_ID);
    }
}
