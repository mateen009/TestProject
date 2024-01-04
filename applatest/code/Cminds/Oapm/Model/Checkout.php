<?php
namespace Cminds\Oapm\Model;

use Cminds\Oapm\Exception\Exception;
use Cminds\Oapm\Exception\InvalidOrderException;

class Checkout
{
    /**
     * @var \Cminds\Oapm\Helper\Config
     */
    protected $helperConfig;

    /**
     * @var \Cminds\Oapm\Model\OrderFactory
     */
    protected $oapmOrderFactory;

    /**
     * @var \Cminds\Oapm\Model\ResourceModel\Order
     */
    protected $oapmOrder;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteCartRepository;

    /**
     * @var \Magento\Checkout\Model\Session $checkoutSession
     */
    protected $checkoutSession;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Cminds\Oapm\Helper\Config $helperConfig
     * @param \Cminds\Oapm\Model\OrderFactory $oapmOrderFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteCartRepository
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Cminds\Oapm\Helper\Config $helperConfig,
        \Cminds\Oapm\Model\OrderFactory $oapmOrderFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Quote\Api\CartRepositoryInterface $quoteCartRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->helperConfig = $helperConfig;
        $this->oapmOrderFactory = $oapmOrderFactory;
        $this->dateTime = $dateTime;
        $this->orderRepository = $orderRepository;
        $this->quoteCartRepository = $quoteCartRepository;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
    }

    /**
     * Hash setter.
     *
     * @param   string $hash
     * @return  \Cminds\Oapm\Model\Checkout
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Hash getter.
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    public function getOapmOrder()
    {
        return $this->oapmOrder;
    }

    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Validate oapm order.
     *
     * @return \Cminds\Oapm\Model\Checkout
     * @throws \Cminds\Oapm\Exception\Exception
     * @throws \Cminds\Oapm\Exception\InvalidOrderException
     */
    protected function validateOrder()
    {
        $hash = $this->getHash();
        if (empty($hash)) {
            throw new Exception(__('Hash is not set, order can not be finalized.'));
        }

        $this->oapmOrder = $this->oapmOrderFactory->create()->loadByHash($hash);

        if (is_null($this->getOapmOrder()->getOrderId())) {
            throw new Exception(__('Order placed using oapm payment method can not be found.'));
        }

        if ((int) $this->getOapmOrder()->getStatus() !== \Cminds\Oapm\Model\Order::STATUS_ACTIVE) {
            throw new InvalidOrderException(__('Order status does not allow for further order processing.'));
        }

        if ($this->helperConfig->isOrderLifetimeUnlimited() === false) {
            $createdAt = $this->getOapmOrder()->getCreatedAt();
            $currentTime = $this->dateTime->gmtDate();

            $timeDiff = abs(strtotime($currentTime) - strtotime($createdAt));
            $hoursDiff = floor($timeDiff / 3600);

            $orderLifetime = $this->helperConfig->getOrderLifetime();

            if ($hoursDiff >= $orderLifetime) {
                $this->getOapmOrder()
                    ->setStatus(\Cminds\Oapm\Model\Order::STATUS_CANCELED)
                    ->save();

                throw new InvalidOrderException(__('Order status does not allow for further order processing.'));
            }
        }

        $this->order = $this->orderRepository->get($this->getOapmOrder()->getOrderId());

        if (is_null($this->getOrder()->getId())) {
            throw new Exception(__('Order which is bound to provided hash can not be found.'));
        }

        return $this;
    }

    /**
     * Prepare quote.
     *
     * @return \Cminds\Oapm\Model\Checkout
     * @throws \Cminds\Oapm\Exception\Exception
     * @throws \Cminds\Oapm\Exception\InvalidOrderException
     */
    public function prepareQuote()
    {
        $this->validateOrder();

        $oapmOrder = $this->getOapmOrder();
        $order = $this->getOrder();

        $quote = $this->quoteCartRepository->get($order->getQuoteId());
        if (is_null($quote->getId())) {
            throw new Exception(__('Quote which is bound to provided hash can not be found.'));
        }

        $quote
            ->setOrigOrderId($order->getId())
            ->setIsActive(1)
            ->save();

        $this->checkoutSession->setQuoteId($quote->getId());

        $this->checkoutSession->setOapmOrderId($oapmOrder->getOrderId());
        $this->checkoutSession->setOapmOrderQuoteId($quote->getId());
        $this->checkoutSession->setOapmQuoteIsGuest($quote->getCustomerIsGuest());

        return $this;
    }

    /**
     * Return checkout path.
     *
     * @return string
     */
    public function getCheckoutPath()
    {
        return $this->storeManager->getStore()->getUrl('checkout');
    }

    /**
     * Cancel order.
     *
     * @return \Cminds\Oapm\Model\Checkout
     */
    public function cancelOrder()
    {
        $this->validateOrder();

        $this->getOapmOrder()
            ->setStatus(\Cminds\Oapm\Model\Order::STATUS_CANCELED)
            ->save();

        return $this;
    }
}
