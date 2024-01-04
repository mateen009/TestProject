<?php
namespace Cminds\Oapm\Observer;

use Cminds\Oapm\Exception\Exception;
use Magento\Checkout\Model\Session;

/**
 * Class CheckoutSubmitAllAfterObserver
 * @package Cminds\Oapm\Observer
 */
class CheckoutSubmitAllAfterObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session $checkoutSession
     */
    protected $checkoutSession;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if( $this->checkoutSession->unsOapmOrderQuoteId() ) {
            $this->checkoutSession->unsOapmOrderId();
            $this->checkoutSession->unsOapmOrderQuoteId();
            $this->checkoutSession->unsOapmQuoteIsGuest();
        }
        return;
    }
}
