<?php

namespace Amasty\RmaAutomation\Model\AutomationRule;

use Amasty\RmaAutomation\Api\Data\RuleActionInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class RuleAction
 */
class RuleAction extends AbstractModel implements RuleActionInterface
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\RuleAction::class);
        $this->setIdFieldName(RuleActionInterface::ACTION_ID);
    }

    /**
     * @inheritdoc
     */
    public function setActionId($id)
    {
        return $this->setData(RuleActionInterface::ACTION_ID, (int)$id);
    }

    /**
     * @inheritdoc
     */
    public function getActionId()
    {
        return (int)$this->_getData(RuleActionInterface::ACTION_ID);
    }

    /**
     * @inheritdoc
     */
    public function setRuleId($id)
    {
        return $this->setData(RuleActionInterface::RULE_ID, (int)$id);
    }

    /**
     * @inheritdoc
     */
    public function getRuleId()
    {
        return (int)$this->_getData(RuleActionInterface::RULE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        return $this->setData(RuleActionInterface::TYPE, $type);
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->_getData(RuleActionInterface::TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setValue($value)
    {
        return $this->setData(RuleActionInterface::VALUE, (int)$value);
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return (int)$this->_getData(RuleActionInterface::VALUE);
    }

    /**
     * @inheritdoc
     */
    public function setAdditionalData($data)
    {
        return $this->setData(RuleActionInterface::ADDITIONAL_DATA, $data);
    }

    /**
     * @inheritdoc
     */
    public function getAdditionalData()
    {
        return $this->_getData(RuleActionInterface::ADDITIONAL_DATA);
    }
}
