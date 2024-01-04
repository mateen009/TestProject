<?php

namespace Amasty\RmaAutomation\Cron;

use Amasty\RmaAutomation\Model\AutomationRule\AutomationRuleProcessor;

/**
 * Class RmaUpdate
 */
class RmaUpdate
{
    /**
     * @var AutomationRuleProcessor
     */
    private $ruleProcessor;

    /**
     * @param AutomationRuleProcessor $ruleProcessor
     */
    public function __construct(
        AutomationRuleProcessor $ruleProcessor
    ) {
        $this->ruleProcessor = $ruleProcessor;
    }

    /**
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $this->ruleProcessor->processAllRma();
    }
}
