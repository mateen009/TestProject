<?php
namespace Cminds\Oapm\Plugin\Sales\Model\Order\Email\Sender;

class OrderSender
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    public function aroundSend(\Magento\Sales\Model\Order\Email\Sender\OrderSender $subject,
        callable $proceed,
        \Magento\Sales\Model\Order $order,
        $forceSyncMode = false
    )
	{
        $quote = $this->checkoutSession->getQuote();
        $paymentMethod = $quote->getPayment()->getMethod();
        if ($paymentMethod === \Cminds\Oapm\Model\Payment\Oapm::METHOD_CODE) {
            return false;
        }

        $oapmOrderQuoteId = $this->checkoutSession->getOapmOrderQuoteId();
        if ($oapmOrderQuoteId && $oapmOrderQuoteId === $order->getQuoteId()) {
            return false;
        }

		return $proceed($order, $forceSyncMode );
	}
}