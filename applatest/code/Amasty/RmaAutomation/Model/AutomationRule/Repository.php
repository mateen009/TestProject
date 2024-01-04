<?php

namespace Amasty\RmaAutomation\Model\AutomationRule;

use Amasty\Rma\Model\OptionSource\Status;
use Amasty\RmaAutomation\Api\AutomationRuleRepositoryInterface;
use Amasty\RmaAutomation\Api\Data\AutomationRuleInterface;
use Amasty\RmaAutomation\Api\Data\AutomationRuleInterfaceFactory;
use Amasty\RmaAutomation\Api\Data\RuleActionInterface;
use Amasty\RmaAutomation\Api\Data\RuleActionInterfaceFactory;
use Amasty\RmaAutomation\Model\AutomationRule\ResourceModel\AutomationRule;
use Amasty\RmaAutomation\Model\AutomationRule\ResourceModel\CollectionFactory;
use Amasty\RmaAutomation\Model\AutomationRule\ResourceModel\RuleAction;
use Amasty\RmaAutomation\Model\AutomationRule\ResourceModel\RuleActionCollectionFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Repository
 */
class Repository implements AutomationRuleRepositoryInterface
{
    /**
     * @var AutomationRuleInterfaceFactory
     */
    private $automationRuleFactory;

    /**
     * @var AutomationRule
     */
    private $automationRuleResource;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var RuleActionInterfaceFactory
     */
    private $ruleActionFactory;

    /**
     * @var RuleAction
     */
    private $ruleActionResource;

    /**
     * @var RuleActionCollectionFactory
     */
    private $ruleActionCollectionFactory;

    /**
     * Model storage
     * @var AutomationRuleInterface[]
     */
    private $automationRules = [];

    /**
     * Storage for active rules
     * @var AutomationRuleInterface[]
     */
    private $activeRules = [];

    public function __construct(
        AutomationRuleInterfaceFactory $automationRuleFactory,
        AutomationRule $automationRuleResource,
        CollectionFactory $collectionFactory,
        RuleActionInterfaceFactory $ruleActionFactory,
        RuleAction $ruleActionResource,
        RuleActionCollectionFactory $ruleActionCollectionFactory
    ) {
        $this->automationRuleFactory = $automationRuleFactory;
        $this->automationRuleResource = $automationRuleResource;
        $this->collectionFactory = $collectionFactory;
        $this->ruleActionFactory = $ruleActionFactory;
        $this->ruleActionResource = $ruleActionResource;
        $this->ruleActionCollectionFactory = $ruleActionCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(AutomationRuleInterface $rule)
    {
        try {
            if ($rule->getRuleId()) {
                $rule = $this->getById($rule->getId())->addData($rule->getData());
            }
            $this->automationRuleResource->save($rule);
            $this->saveRuleActions($rule);

            unset($this->automationRules[$rule->getRuleId()]);
        } catch (\Exception $e) {
            if ($rule->getRuleId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save rule with ID %1. Error: %2',
                        [$rule->getRuleId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new rule. Error: %1', $e->getMessage()));
        }

        return $rule;
    }

    /**
     * @param AutomationRuleInterface $rule
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    private function saveRuleActions($rule)
    {
        /** @var ResourceModel\RuleActionCollection $ruleActionCollection */
        $ruleActionCollection = $this->ruleActionCollectionFactory->create();
        $ruleActionCollection->addFieldToFilter(
            RuleActionInterface::RULE_ID,
            $rule->getRuleId()
        );
        $ruleActionCollection->walk('delete');

        if ($actions = $rule->getRuleActions()) {
            foreach ($actions as $action) {
                $action->setRuleId($rule->getRuleId());
                $this->ruleActionResource->save($action);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getById($ruleId)
    {
        if (!isset($this->automationRules[$ruleId])) {
            /** @var AutomationRuleInterface $rule */
            $rule = $this->automationRuleFactory->create();
            $this->automationRuleResource->load($rule, $ruleId);

            if (!$rule->getRuleId()) {
                throw new NoSuchEntityException(__('Rule with specified ID "%1" not found.', $ruleId));
            }
            /** @var ResourceModel\RuleActionCollection $ruleActionCollection */
            $ruleActionCollection = $this->ruleActionCollectionFactory->create();
            $ruleActionCollection->addFieldToFilter(
                RuleActionInterface::RULE_ID,
                $rule->getRuleId()
            );
            $rule->setRuleActions($ruleActionCollection->getItems());

            $this->automationRules[$ruleId] = $rule;
        }

        return $this->automationRules[$ruleId];
    }

    /**
     * @inheritdoc
     */
    public function getActiveRules($ruleType = null)
    {
        if (!$this->activeRules) {
            /** @var ResourceModel\Collection $collection */
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter(AutomationRuleInterface::STATUS, Status::ENABLED)
                ->setOrder(
                    AutomationRuleInterface::PRIORITY,
                    \Magento\Framework\Data\Collection::SORT_ORDER_ASC
                );

            switch ($ruleType) {
                case AutomationRuleProcessor::PROCESS_NEW:
                    $collection->addFieldToFilter(AutomationRuleInterface::APPLY_FOR_NEW, 1);
                    break;
                case AutomationRuleProcessor::PROCESS_EXISTING:
                    $collection->addFieldToFilter(AutomationRuleInterface::APPLY_FOR_EXISTING, 1);
                    break;
            }
            $this->activeRules = $collection->getItems();
        }

        return $this->activeRules;
    }

    /**
     * @inheritdoc
     */
    public function delete(AutomationRuleInterface $rule)
    {
        try {
            $this->automationRuleResource->delete($rule);
            unset($this->automationRules[$rule->getRuleId()]);
        } catch (\Exception $e) {
            if ($rule->getRuleId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove rule with ID %1. Error: %2',
                        [$rule->getRuleId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove rule. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($ruleId)
    {
        $rule = $this->getById($ruleId);

        $this->delete($rule);
    }

    /**
     * @inheritdoc
     */
    public function getEmptyRuleModel()
    {
        return $this->automationRuleFactory->create();
    }

    /**
     * @inheritdoc
     */
    public function getEmptyRuleActionModel()
    {
        return $this->ruleActionFactory->create();
    }

    public function getActionsByRuleId($ruleId)
    {
        /** @var ResourceModel\RuleActionCollection $ruleActionsCollection */
        $ruleActionsCollection = $this->ruleActionCollectionFactory->create();
        $ruleActionsCollection->addFieldToFilter(RuleActionInterface::RULE_ID, $ruleId);

        return $ruleActionsCollection->getData();
    }
}
