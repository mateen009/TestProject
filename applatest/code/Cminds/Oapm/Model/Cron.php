<?php
namespace Cminds\Oapm\Model;

class Cron
{
    /**
     *  @var \Cminds\Oapm\Helper\Data
     */
    protected $helper;

    /**
     *  @var \Cminds\Oapm\Helper\Config
     */
    protected $helperConfig;

    /**
     *  @var \Cminds\Oapm\Model\ResourceModel\Order\CollectionFactory
     */
    protected $oapmOrderCollectionFactory;

    /**
     *  @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     *  @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    public function __construct(
        \Cminds\Oapm\Helper\Data $helper,
        \Cminds\Oapm\Helper\Config $helperConfig,
        \Cminds\Oapm\Model\ResourceModel\Order\CollectionFactory $oapmOrderCollectionFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        $this->helper = $helper;
        $this->helperConfig = $helperConfig;
        $this->oapmOrderCollectionFactory = $oapmOrderCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->dateTime = $dateTime;
    }

    /**
     * Disable oapm orders depends of configured lifetime.
     *
     * @return \Cminds\Oapm\Model\Cron
     */
    public function clean()
    {
        if ($this->helperConfig->isOrderLifetimeUnlimited()) {
            return $this;
        }

        $currentTime = $this->dateTime->gmtDate();
        $orderLifetime = $this->helperConfig->getOrderLifetime();

        /** @var \Cminds\Oapm\Model\ResourceModel\Order\Collection $collection */
        $collection = $this->oapmOrderCollectionFactory->create()
            ->filterByStatus(\Cminds\Oapm\Model\Order::STATUS_ACTIVE);

        foreach ($collection as $oapmOrder) {
            $createdAt = $oapmOrder->getCreatedAt();

            $timeDiff = abs(strtotime($currentTime) - strtotime($createdAt));
            $hoursDiff = floor($timeDiff / 3600);

            if ($hoursDiff >= $orderLifetime) {
                $oapmOrder
                    ->setStatus(\Cminds\Oapm\Model\Order::STATUS_CANCELED)
                    ->save();
            }
        }

        return $this;
    }

    /**
     * Process reminders.
     *
     * @return \Cminds\Oapm\Model\Cron
     */
    public function processReminders()
    {
        $intervals = $this->helperConfig->getReminderIntervals();
        if (empty($intervals)) {
            return $this;
        }

        $currentTime = $this->dateTime->gmtDate();

        $intervalsCount = count($intervals);
        $leftRange = $intervals[$intervalsCount - 1];
        $rightRange = $intervals[0];

        /** @var \Cminds\Oapm\Model\ResourceModel\Order\Collection $collection */
        $collection = $this->oapmOrderCollectionFactory->create()
            ->filterByStatus(\Cminds\Oapm\Model\Order::STATUS_ACTIVE);

        $collection
            ->getSelect()
            ->where(sprintf('? - interval %s hour <= created_at', $leftRange), $currentTime)
            ->where(sprintf('? - interval %s hour >= created_at', $rightRange), $currentTime);

        foreach ($collection as $oapmOrder) {
            $timeDiff = abs(strtotime($currentTime) - strtotime($oapmOrder->getCreatedAt()));
            $hourDiff = floor($timeDiff / 3600);

            for ($i = 0; $i < $intervalsCount; $i++) {
                if ($hourDiff !== $intervals[$i]) {
                    continue;
                }

                $order = $this->orderRepository->get($oapmOrder->getOrderId());
                $payment = $order->getPayment();

                $billingAddress = $order->getBillingAddress();
                $customerName = $billingAddress->getFirstname() . ' ' . $billingAddress->getLastname();
                $customerEmail = $billingAddress->getEmail();

                $checkoutUrl = $this->helper->getCheckoutUrl($oapmOrder->getHash());
                $cancelUrl = $this->helper->getCancelUrl($oapmOrder->getHash());

                if (($i + 1) < $intervalsCount) {
                    $this->helper->sendOrderPendingReminderPayerNotification(
                        [
                            'creator_name' => $customerName,
                            'creator_email' => $customerEmail,
                            'payer_name' => $payment->getAdditionalInformation('recipient_name'),
                            'payer_email' => $payment->getAdditionalInformation('recipient_email'),
                            'checkout_url' => $checkoutUrl,
                            'cancel_url' => $cancelUrl,
                            'order' => $order
                        ]
                    );
                } else {
                    $this->helper->sendOrderPendingLastReminderPayerNotification(
                        [
                            'creator_name' => $customerName,
                            'creator_email' => $customerEmail,
                            'payer_name' => $payment->getAdditionalInformation('recipient_name'),
                            'payer_email' => $payment->getAdditionalInformation('recipient_email'),
                            'checkout_url' => $checkoutUrl,
                            'cancel_url' => $cancelUrl,
                            'order' => $order
                        ]
                    );

                    $this->helper->sendOrderPendingLastReminderCreatorNotification(
                        [
                            'creator_name' => $customerName,
                            'creator_email' => $customerEmail,
                            'payer_name' => $payment->getAdditionalInformation('recipient_name'),
                            'payer_email' => $payment->getAdditionalInformation('recipient_email')
                        ]
                    );
                }

                break;
            }
        }

        return $this;
    }
}
