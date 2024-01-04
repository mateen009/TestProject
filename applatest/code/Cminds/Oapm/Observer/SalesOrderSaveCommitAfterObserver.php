<?php
namespace Cminds\Oapm\Observer;

use Cminds\Oapm\Exception\Exception;

/**
 * Class SalesOrderSaveCommitAfterObserver
 * @package Cminds\Oapm\Observer
 */
class SalesOrderSaveCommitAfterObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Cminds\Oapm\Helper\Data
     */
    protected $helper;

    /**
     * @var \Cminds\Oapm\Model\OrderFactory $oapmOrderFactory
     */
    protected $oapmOrderFactory;

    /**
     * @param \Cminds\Oapm\Helper\Data $helper
     */
    public function __construct(
        \Cminds\Oapm\Helper\Data $helper,
        \Cminds\Oapm\Model\OrderFactory $oapmOrderFactory
    ) {
        $this->helper = $helper;
        $this->oapmOrderFactory = $oapmOrderFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        $payment = $order->getPayment();

        if ($payment->getMethod() !== \Cminds\Oapm\Model\Payment\Oapm::METHOD_CODE) {
            return;
        }

        if ($order->getOrigData('state') === $order->getData('state')
            || ($order->getOrigData('state') !== $order->getState()
                && $order->getState() !== \Magento\Sales\Model\Order::STATE_CANCELED)
        ) {
            return $this;
        }

        $oapmOrder = $this->oapmOrderFactory->create()->loadByOrderId($order->getId());

        if ($oapmOrder->getId() === null) {
            throw new Exception(__('Order placed using oapm payment method can not be found.'));
        }

        $oapmOrder
            ->setStatus(\Cminds\Oapm\Model\Order::STATUS_CANCELED)
            ->save();

        $billingAddress = $order->getBillingAddress();
        $customerName = $billingAddress->getFirstname() . ' ' . $billingAddress->getLastname();
        $customerEmail = $billingAddress->getEmail();

        $this->helper->sendOrderCanceledCreatorNotification(
            [
                'creator_name' => $customerName,
                'creator_email' => $customerEmail,
                'payer_name' => $payment->getAdditionalInformation('recipient_name')
            ]
        );

        $this->helper->sendOrderCanceledPayerNotification(
            [
                'creator_name' => $customerName,
                'payer_name' => $payment->getAdditionalInformation('recipient_name'),
                'payer_email' => $payment->getAdditionalInformation('recipient_email')
            ]
        );

        return;
    }
}
