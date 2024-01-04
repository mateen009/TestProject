<?php

namespace Amasty\RmaAutomation\Model\AutomationRule\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class RuleActionCollection
 */
class RuleActionCollection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Amasty\RmaAutomation\Model\AutomationRule\RuleAction::class,
            \Amasty\RmaAutomation\Model\AutomationRule\ResourceModel\RuleAction::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
