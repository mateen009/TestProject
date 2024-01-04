<?php
namespace Cminds\Oapm\Plugin\Checkout\Model\Checkout;

use Magento\Checkout\Model\Session as CheckoutSession;

class LayoutProcessor
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
    * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
    * @param array $jsLayout
    * @return array
    */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array  $jsLayout
    ) {

        if(
            $this->checkoutSession->getOapmOrderQuoteId() // may return value even after the order was finalized, so need to check the orderId
            && $originalOrderId = $this->checkoutSession->getQuote()->getOrigOrderId()
        ) {

            $quoteShippingAddress = $this->checkoutSession->getQuote()->getShippingAddress();

            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                ['shippingAddress']['children']['customer-email']['value'] = $this->checkoutSession->getQuote()->getEmail();

            // remove the customer email block, the value is added via js
            // see view\frontend\web\js\view\email-autofill-logic.js
            unset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['customer-email']);

            $targetValues = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                ['shippingAddress']['children']['shipping-address-fieldset']['children'];

            // populate shippingAddress form with order data
            foreach( $targetValues as $key => $value ) {
                if( $quoteShippingAddress->getData($key) ){
                    // fix for 2 to 4 line street
                    if( 'street' === $key ) {
                        $streetData = $quoteShippingAddress->explodeStreetAddress()->getStreet();
                        foreach( [0,1,2,3] as $index ) {
                            if( isset($streetData[ $index ]) )
                                $targetValues[ $key ]['children'][ $index ]['value'] = $streetData[ $index ];
                        }
                    } else {
                        $targetValues[ $key ]['value'] = $quoteShippingAddress->getData($key);
                    }
                }
            }

            $quoteBillingAddress = $this->checkoutSession->getQuote()->getBillingAddress();

            if($quoteBillingAddress) {

                $targetValues = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'];
                foreach($targetValues as $paymentBlockKey => $paymentBlockValue) {
                    // skip blocks without forms
                    if(!isset($paymentBlockValue['children'])
                        || !isset($paymentBlockValue['children']['form-fields'])
                    ) {
                        continue;
                    }
                    // fill out each form
                    foreach($paymentBlockValue['children']['form-fields']['children'] as $key => $value) {
                        if($quoteBillingAddress->getData($key)){
                            // fix for 2 to 4 line street
                            if( 'street' === $key ) {
                                $streetData = $quoteBillingAddress->explodeStreetAddress()->getStreet();
                                foreach( [0,1,2,3] as $index ) {
                                    if( isset($streetData[ $index ]) )
                                        $targetValues[$paymentBlockKey]['children']['form-fields']['children'][ $key ]['children'][ $index ]['value'] = $streetData[ $index ];
                                }
                            } else {
                                $targetValues[$paymentBlockKey]['children']['form-fields']['children'][ $key ]['value'] = $quoteBillingAddress->getData($key);
                            }
                        }
                    }
                }
            }
        } else {
            // remove Cminds_Oapm/js/view/email-autofill-logic.js block added in checkout_index_index
            unset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['before-form']['children']['cminds-oapm-email-autofill']);
        }

        return $jsLayout;
    }
}