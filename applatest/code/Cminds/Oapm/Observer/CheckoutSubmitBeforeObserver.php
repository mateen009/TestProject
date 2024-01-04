<?php
namespace Cminds\Oapm\Observer;

use Cminds\Oapm\Exception\Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Cminds\Oapm\Helper\Config as OapmConfig;

/**
 * Class CheckoutSubmitBeforeObserver
 * @package Cminds\Oapm\Observer
 */
class CheckoutSubmitBeforeObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var CheckoutSession $checkoutSession
     */
    protected $checkoutSession;

    /**
     * @var OapmConfig
     */
    protected $helperConfig;

    /**
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        OapmConfig $helperConfig
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->helperConfig = $helperConfig;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if(
            $this->helperConfig->isEnabled()
            && $this->checkoutSession->getData('oapm_order_id')
        ){
            // prevent billing address from being saved in customer address book
            $observer->getQuote()
                ->getBillingAddress()
                ->setSaveInAddressBook(0);

            // prevent shipping address from being saved in customer address book
            $observer->getQuote()
                ->getShippingAddress()
                ->setSaveInAddressBook(0);
        }

    }
}
