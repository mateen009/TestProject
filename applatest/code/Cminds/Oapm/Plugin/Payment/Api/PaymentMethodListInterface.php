<?php
namespace Cminds\Oapm\Plugin\Payment\Api;

use Magento\Checkout\Model\Session as CheckoutSession;
use Cminds\Oapm\Helper\Config as OapmConfig;

class PaymentMethodListInterface
{
    /**
     * @var OapmConfig
     */
    protected $helperConfig;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @param OapmConfig $helperConfig,
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        OapmConfig $helperConfig,
        CheckoutSession $checkoutSession
    ) {
        $this->helperConfig = $helperConfig;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param Magento\Payment\Api\PaymentMethodListInterface $subject
     * @param \Magento\Payment\Api\Data\PaymentMethodInterface[] $result
     * @param int $storeId
     * @return \Magento\Payment\Api\Data\PaymentMethodInterface[]
     */
    public function afterGetActiveList(
        \Magento\Payment\Api\PaymentMethodListInterface $subject,
        $result,
        $storeId
    ) {
        $methods = $result;

        $oapmOrderQuoteId = $this->checkoutSession->getOapmOrderQuoteId();
        $oapmAdminApprover = (int)$this->helperConfig->getConfigData('approver');


        // if (
        //     $oapmOrderQuoteId
        //     && $oapmOrderQuoteId === $this->checkoutSession->getQuoteId()
        //     || $this->helperConfig->getAdminAprover() === $oapmAdminApprover
        // ) {
        //     foreach ($methods as $index => $method) {
        //         if (
        //             $method->getCode() === $this->helperConfig->getCode()
        //         ) {
        //             if( $oapmOrderQuoteId === $this->checkoutSession->getQuoteId() )
        //                 unset($methods[$index]);
        //         } else if(
        //             $this->helperConfig->getAdminAprover() === $oapmAdminApprover
        //             && $oapmOrderQuoteId !== $this->checkoutSession->getQuoteId()
        //         ) {
        //             unset($methods[$index]);
        //         }
        //     }
        // }

        return $methods;
    }
}
