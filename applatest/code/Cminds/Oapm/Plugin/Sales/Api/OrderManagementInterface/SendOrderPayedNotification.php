<?php
namespace Cminds\Oapm\Plugin\Sales\Api\OrderManagementInterface;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Checkout\Model\Session;
use Cminds\Oapm\Helper\Data as OapmHelper;
use Cminds\Oapm\Model\OrderFactory as OapmOrderFactory;

class SendOrderPayedNotification
{
    /**
     * @var CartRepositoryInterface
     */
    protected $quoteCartRepository;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var OapmHelper
     */
    protected $helper;

    /**
     * @var OapmOrderFactory
     */
    protected $oapmOrderFactory;

    /**
     * @param CartRepositoryInterface $quoteCartRepository,
     * @param Session $checkoutSession,
     * @param OapmHelper $helper,
     * @param OapmOrderFactory $oapmOrderFactory
     */
    public function __construct(
        CartRepositoryInterface $quoteCartRepository,
        Session $checkoutSession,
        OapmHelper $helper,
        OapmOrderFactory $oapmOrderFactory
    ) {
        $this->quoteCartRepository = $quoteCartRepository;
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
        $this->oapmOrderFactory = $oapmOrderFactory;
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
        $quote = $this->quoteCartRepository->get($order->getQuoteId());
        $oapmQuoteId = $this->checkoutSession->getOapmOrderQuoteId();
        if (! $oapmQuoteId) {
            return $result;
        }
        if ($oapmQuoteId !== $quote->getId()) {
            return $result;
        }

        $payment = $order->getPayment();

        $oapmOrderId = $this->checkoutSession->getOapmOrderId();
        if($oapmOrderId){
            // get Oapm order data object
            $oapmOrder = $this->oapmOrderFactory->create()->loadByOrderId($oapmOrderId);
            $oapmOrder
                ->setStatus(\Cminds\Oapm\Model\Order::STATUS_FINALIZED)
                ->save();
        }

        $billingAddress = $order->getBillingAddress();
        $customerName = $billingAddress->getFirstname() . ' ' . $billingAddress->getLastname();
        $payerName = $payment->getAdditionalInformation('recipient_name');

        $this->helper->sendOrderPayedCreatorNotification(
            [
                'creator_name' => $customerName,
                'creator_email' => $billingAddress->getEmail()
            ]
        );

        $this->helper->sendOrderPayedPayerNotification(
            [
                'payer_name' => $payerName,
                'payer_email' => $payment->getAdditionalInformation('recipient_email'),
                'creator_name' => $customerName
            ]
        );

        $order->addStatusHistoryComment(__('Order has been payed by: %1.', $payerName))
                ->setIsVisibleOnFront(false)
                ->setIsCustomerNotified(false);

        $order->save();

        return $result;
    }
}
