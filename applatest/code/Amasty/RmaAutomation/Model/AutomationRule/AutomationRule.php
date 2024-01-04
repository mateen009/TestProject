<?php

namespace Amasty\RmaAutomation\Model\AutomationRule;

use Amasty\RmaAutomation\Api\Data\AutomationRuleInterface;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Rule\Model\AbstractModel;

/**
 * Class AutomationRule
 */
class AutomationRule extends AbstractModel implements AutomationRuleInterface
{
    /**#@+
     * Constants
     */
    const CURRENT_RETURN_RULE = 'current_amrmaaut_automationrule';
    const FORM_NAMESPACE = 'amrmaaut_automationrule_form';
    /**#@-*/

    protected $_eventPrefix = 'automation_rule';

    protected $_eventObject = 'rule';

    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\CombineFactory
     */
    private $combineFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Amasty\RmaAutomation\Model\AutomationRule\Condition\CombineFactory $combineFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
        );
        $this->combineFactory = $combineFactory;
    }

    /**
     * Model Init
     *
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Amasty\RmaAutomation\Model\AutomationRule\ResourceModel\AutomationRule::class);
        $this->setIdFieldName(AutomationRuleInterface::RULE_ID);
    }

    /**
     * @inheritdoc
     */
    public function getConditionsInstance()
    {
        return $this->combineFactory->create();
    }

    /**
     * @inheritdoc
     */
    public function getActionsInstance()
    {
        return $this->combineFactory->create();
    }

    /**
     * @inheritdoc
     */
    public function setRuleId($id)
    {
        return $this->setData(AutomationRuleInterface::RULE_ID, (int)$id);
    }

    /**
     * @inheritdoc
     */
    public function getRuleId()
    {
        return (int)$this->_getData(AutomationRuleInterface::RULE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        return $this->setData(AutomationRuleInterface::NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->_getData(AutomationRuleInterface::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        return $this->setData(AutomationRuleInterface::STATUS, (int)$status);
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return (int)$this->_getData(AutomationRuleInterface::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setPriority($priority)
    {
        return $this->setData(AutomationRuleInterface::PRIORITY, (int)$priority);
    }

    /**
     * @inheritdoc
     */
    public function getPriority()
    {
        return (int)$this->_getData(AutomationRuleInterface::PRIORITY);
    }

    /**
     * @inheritdoc
     */
    public function setStopFurtherProcessing($process)
    {
        return $this->setData(AutomationRuleInterface::FURTHER_PROCESSING, $process);
    }

    /**
     * @inheritdoc
     */
    public function isStopFurtherProcessing()
    {
        return (bool)$this->_getData(AutomationRuleInterface::FURTHER_PROCESSING);
    }

    /**
     * @inheritdoc
     */
    public function setApplyForNew($apply)
    {
        return $this->setData(AutomationRuleInterface::APPLY_FOR_NEW, $apply);
    }

    /**
     * @inheritdoc
     */
    public function isApplyForNew()
    {
        return (bool)$this->_getData(AutomationRuleInterface::APPLY_FOR_NEW);
    }

    /**
     * @inheritdoc
     */
    public function setApplyForExisting($apply)
    {
        return $this->setData(AutomationRuleInterface::APPLY_FOR_EXISTING, $apply);
    }

    /**
     * @inheritdoc
     */
    public function isApplyForExisting()
    {
        return (bool)$this->_getData(AutomationRuleInterface::APPLY_FOR_EXISTING);
    }

    /**
     * @inheritdoc
     */
    public function setConditionsSerialized($conditions)
    {
        return $this->setData(AutomationRuleInterface::CONDITIONS_SERIALIZED, $conditions);
    }

    /**
     * @inheritdoc
     */
    public function getConditionsSerialized()
    {
        return $this->_getData(AutomationRuleInterface::CONDITIONS_SERIALIZED);
    }

    /**
     * @inheritdoc
     */
    public function setRuleActions($actions)
    {
        return $this->setData(AutomationRuleInterface::RULE_ACTIONS, $actions);
    }

    /**
     * @inheritdoc
     */
    public function getRuleActions()
    {
        return $this->_getData(AutomationRuleInterface::RULE_ACTIONS);
    }
}
