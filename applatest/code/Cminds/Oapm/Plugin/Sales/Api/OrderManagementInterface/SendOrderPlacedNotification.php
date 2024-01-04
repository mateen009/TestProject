<?php
namespace Cminds\Oapm\Plugin\Sales\Api\OrderManagementInterface;

class SendOrderPlacedNotification
{
    /**
     * @var \Cminds\Oapm\Model\OrderFactory
     */
    protected $oapmOrderFactory;

    /**
     * @var \Cminds\Oapm\Helper\Data
     */
    protected $helper;

    /**
     * @param \Cminds\Oapm\Model\OrderFactory $oapmOrderFactory
     * @param \Cminds\Oapm\Helper\Data $helper
     */
    public function __construct(
        \Cminds\Oapm\Model\OrderFactory $oapmOrderFactory,
        \Cminds\Oapm\Helper\Data $helper
    ) {
        $this->oapmOrderFactory = $oapmOrderFactory;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Sales\Api\OrderManagementInterface $subject
     * @param $result
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function afterPlace(
        \Magento\Sales\Api\OrderManagementInterface $subject,
        $result,
        \Magento\Sales\Api\Data\OrderInterface $order
    ) {
        $payment = $order->getPayment();

        if ($payment->getMethod() !== \Cminds\Oapm\Model\Payment\Oapm::METHOD_CODE) {
            return $result;
        }

        $oapmOrder = $this->oapmOrderFactory->create();
        $oapmOrder->setOrderId($order->getId())
            ->save();

        $billingAddress = $order->getBillingAddress();
        $customerName = $billingAddress->getFirstname() . ' ' . $billingAddress->getLastname();
        $customerEmail = $billingAddress->getEmail();

        $checkoutUrl = $this->helper->getCheckoutUrl($oapmOrder->getHash());
        $cancelUrl = $this->helper->getCancelUrl($oapmOrder->getHash());

        $this->helper->sendOrderPlacedPayerNotification(
            [
                'creator_name' => $customerName,
                'creator_email' => $customerEmail,
                'payer_name' => $payment->getAdditionalInformation('recipient_name'),
                'payer_email' => $payment->getAdditionalInformation('recipient_email'),
                'payer_note' => $payment->getAdditionalInformation('recipient_note'),
                'checkout_url' => $checkoutUrl,
                'cancel_url' => $cancelUrl,
                'order' => $order
            ]
        );

        $this->helper->sendOrderPlacedCreatorNotification(
            [
                'creator_name' => $customerName,
                'creator_email' => $customerEmail,
                'payer_name' => $payment->getAdditionalInformation('recipient_name'),
                'payer_email' => $payment->getAdditionalInformation('recipient_email'),
                'order' => $order
            ]
        );

        $order->addStatusHistoryComment(
                    __('Customer has placed order using OAPM payment method.')
                    . '<br/>' . __('Recipient Name') . ": "
                    . $payment->getAdditionalInformation('recipient_name')
                    . '<br/>' . __('Recipient Email') . ": "
                    . $payment->getAdditionalInformation('recipient_email')
                    . '<br/>' . __('Note To Recipient') . ": "
                    . $payment->getAdditionalInformation('recipient_note')
                )
                ->setIsVisibleOnFront(false)
                ->setIsCustomerNotified(false);

        $order->addStatusHistoryComment(__('Order placed notification has been sent.'))
                ->setIsVisibleOnFront(false)
                ->setIsCustomerNotified(false);

        $order->save();

        return $result;
    }
}
