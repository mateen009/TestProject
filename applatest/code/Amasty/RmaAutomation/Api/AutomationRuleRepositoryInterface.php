<?php

namespace Amasty\RmaAutomation\Api;

/**
 * Interface AutomationRuleRepositoryInterface
 */
interface AutomationRuleRepositoryInterface
{
    /**
     * @param \Amasty\RmaAutomation\Api\Data\AutomationRuleInterface $rule
     *
     * @return \Amasty\RmaAutomation\Api\Data\AutomationRuleInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Amasty\RmaAutomation\Api\Data\AutomationRuleInterface $rule);

    /**
     * @param int $ruleId
     *
     * @return \Amasty\RmaAutomation\Api\Data\AutomationRuleInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($ruleId);

    /**
     * @param int|null $ruleType
     *
     * @return \Amasty\RmaAutomation\Api\Data\AutomationRuleInterface[]
     */
    public function getActiveRules($ruleType);

    /**
     * @param \Amasty\RmaAutomation\Api\Data\AutomationRuleInterface $rule
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\RmaAutomation\Api\Data\AutomationRuleInterface $rule);

    /**
     * @param int $ruleId
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($ruleId);

    /**
     * @return \Amasty\RmaAutomation\Api\Data\AutomationRuleInterface
     */
    public function getEmptyRuleModel();

    /**
     * @return \Amasty\RmaAutomation\Api\Data\RuleActionInterface
     */
    public function getEmptyRuleActionModel();

    /**
     * @param int $ruleId
     *
     * @return array
     */
    public function getActionsByRuleId($ruleId);
}
