<?php
namespace Cminds\Oapm\Plugin\Quote\Model\Webapi;

use \Magento\Checkout\Model\Session as CheckoutSession;

class ParamOverriderCartId
{
    /**
     * @var CheckoutSession $checkoutSession
     */
    protected $checkoutSession;

    /**
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        CheckoutSession $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Quote\Model\Webapi\ParamOverriderCartId $subject
     * @param bool|int
     */

    public function afterGetOverriddenValue(
        \Magento\Quote\Model\Webapi\ParamOverriderCartId $subject, $resultData
    ){
        $oapmQuoteId = $this->checkoutSession->getData('oapm_order_quote_id');
        if(
            $oapmQuoteId
            && $resultData !== $oapmQuoteId
        )
            $resultData = $oapmQuoteId;

        return $resultData;
    }
}