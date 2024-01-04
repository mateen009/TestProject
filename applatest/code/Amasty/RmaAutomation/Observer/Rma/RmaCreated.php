<?php

namespace Amasty\RmaAutomation\Observer\Rma;

use Amasty\RmaAutomation\Model\AutomationRule\AutomationRuleProcessor;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class RmaCreated
 */
class RmaCreated implements ObserverInterface
{
    /**
     * @var AutomationRuleProcessor
     */
    private $ruleProcessor;

    public function __construct(
        AutomationRuleProcessor $ruleProcessor
    ) {
        $this->ruleProcessor = $ruleProcessor;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Amasty\Rma\Api\Data\RequestInterface $request */
        $request = $observer->getRequest();

        if ($request) {
            $this->ruleProcessor->processRma($request, AutomationRuleProcessor::PROCESS_NEW);
        }
    }
}
