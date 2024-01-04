<?php
namespace Cminds\Oapm\Plugin\Checkout\Model;

use Magento\Checkout\Model\Session as CheckoutSession;

class GuestPaymentInformationManagement
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
     * @param CheckoutSession $subject
     * @param array $configData
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        \Magento\Checkout\Model\GuestPaymentInformationManagement $subject,
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress
    ) {
        // if it's the oapm order confirmation case
        if( $this->checkoutSession->getOapmOrderQuoteId()
            && $this->checkoutSession->getQuote()
            && $originalOrderId = $this->checkoutSession->getQuote()->getOrigOrderId()
        ) {
            // check email data reciecved from frontend for guest users
            if( $initialBillingEmail = $this->checkoutSession->getQuote()->getShippingAddress()->getEmail() ){
                if( $initialBillingEmail != $email ){
                    $email = $initialBillingEmail;
                }
            }
        }

        return [$cartId, $email, $paymentMethod, $billingAddress];
    }
}