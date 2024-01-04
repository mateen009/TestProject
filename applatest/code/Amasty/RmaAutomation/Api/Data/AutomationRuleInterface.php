<?php

namespace Amasty\RmaAutomation\Api\Data;

/**
 * Interface AutomationRuleInterface
 */
interface AutomationRuleInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const RULE_ID = 'rule_id';
    const NAME = 'name';
    const STATUS = 'status';
    const PRIORITY = 'priority';
    const FURTHER_PROCESSING = 'further_processing';
    const APPLY_FOR_NEW = 'apply_for_new';
    const APPLY_FOR_EXISTING = 'apply_for_existing';
    const CONDITIONS_SERIALIZED = 'conditions_serialized';
    const RULE_ACTIONS = 'rule_actions';
    /**#@-*/

    /**
     * @param int $id
     *
     * @return \Amasty\RmaAutomation\Api\Data\AutomationRuleInterface
     */
    public function setRuleId($id);

    /**
     * @return int
     */
    public function getRuleId();

    /**
     * @param string $name
     *
     * @return \Amasty\RmaAutomation\Api\Data\AutomationRuleInterface
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param int $status
     *
     * @return \Amasty\RmaAutomation\Api\Data\AutomationRuleInterface
     */
    public function setStatus($status);

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @param int $priority
     *
     * @return \Amasty\RmaAutomation\Api\Data\AutomationRuleInterface
     */
    public function setPriority($priority);

    /**
     * @return int
     */
    public function getPriority();

    /**
     * @param bool $process
     *
     * @return \Amasty\RmaAutomation\Api\Data\AutomationRuleInterface
     */
    public function setStopFurtherProcessing($process);

    /**
     * @return bool
     */
    public function isStopFurtherProcessing();

    /**
     * @param bool $apply
     *
     * @return \Amasty\RmaAutomation\Api\Data\AutomationRuleInterface
     */
    public function setApplyForNew($apply);

    /**
     * @return bool
     */
    public function isApplyForNew();

    /**
     * @param bool $apply
     *
     * @return \Amasty\RmaAutomation\Api\Data\AutomationRuleInterface
     */
    public function setApplyForExisting($apply);

    /**
     * @return bool
     */
    public function isApplyForExisting();

    /**
     * @param string $conditions
     *
     * @return \Amasty\RmaAutomation\Api\Data\AutomationRuleInterface
     */
    public function setConditionsSerialized($conditions);

    /**
     * @return string
     */
    public function getConditionsSerialized();

    /**
     * @param RuleActionInterface[] $actions
     *
     * @return \Amasty\RmaAutomation\Api\Data\AutomationRuleInterface
     */
    public function setRuleActions($actions);

    /**
     * @return RuleActionInterface[]
     */
    public function getRuleActions();
}
