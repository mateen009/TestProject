<?php
namespace Cminds\Oapm\Plugin\Checkout\Model;

class Session
{
    /**
     * @param \Magento\Checkout\Model\Session $subject
     * @param string|null $step
     * @param string|null $data
     */
    public function beforeGetStepData(
        \Magento\Checkout\Model\Session $subject,
        $step = null,
        $data = null
    ) {
        $oapmOrderQuoteId = $subject->getOapmOrderQuoteId();
        $quoteId = $subject->getQuoteId();

        $steps = $subject->getSteps();

        if (! is_null($steps) && $oapmOrderQuoteId === $quoteId) {
            $completedSteps = ['billing', 'shipping', 'shipping_method'];

            foreach ($steps as $stepCode => $stepData) {
                if (in_array($stepCode, $completedSteps)) {
                    $subject->setStepData($stepCode, 'complete', true);
                }
            }
        }

        return [$step, $data];
    }

    public function aroundLoadCustomerQuote(
        \Magento\Checkout\Model\Session $subject,
        callable $proceed
    ){
        $oapmOrderQuoteId = $subject->getOapmOrderQuoteId();
        $quoteId = $subject->getQuoteId();

        if ($oapmOrderQuoteId === $quoteId) {
            return $subject;
        }

        return $proceed();
    }
}
