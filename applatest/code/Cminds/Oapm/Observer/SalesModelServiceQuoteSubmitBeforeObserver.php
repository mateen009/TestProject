<?php
namespace Cminds\Oapm\Observer;

/**
 * Class SalesModelServiceQuoteSubmitBeforeObserver
 * @package Cminds\Oapm\Observer
 */
class SalesModelServiceQuoteSubmitBeforeObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session $checkoutSession
     */
    protected $checkoutSession;

    /**
     * @var \Cminds\Oapm\Helper\Config
     */
    protected $helperConfig;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Sales\Api\OrderAddressRepositoryInterface
     */
    protected $orderAddressRepository;

    /**
     * @var \Magento\Sales\Api\OrderPaymentRepositoryInterface
     */
    protected $orderPaymentRepository;

    /**
     * @var \Magento\Quote\Model\Quote\Payment\ToOrderPayment
     */
    protected $quotePaymentToOrderPayment;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Cminds\Oapm\Helper\Config $helperConfig
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Api\OrderAddressRepositoryInterface $orderAddressRepository
     * @param \Magento\Sales\Api\OrderPaymentRepositoryInterface $orderPaymentRepository
     * @param \Magento\Quote\Model\Quote\Payment\ToOrderPayment $toOrderPayment
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Cminds\Oapm\Helper\Config $helperConfig,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\OrderAddressRepositoryInterface $orderAddressRepository,
        \Magento\Sales\Api\OrderPaymentRepositoryInterface $orderPaymentRepository,
        \Magento\Quote\Model\Quote\Payment\ToOrderPayment $toOrderPayment
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->helperConfig = $helperConfig;
        $this->orderRepository = $orderRepository;
        $this->orderAddressRepository = $orderAddressRepository;
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->quotePaymentToOrderPayment = $toOrderPayment;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @see \Magento\Quote\Model\QuoteManagement::submitQuote()
     * @see \Cminds\Oapm\Preference\Quote\Model\Quote::reserveOrderId() The first part of one task
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (! $this->helperConfig->getConfigData('active')) {
            return $this;
        }

        $oapmOrderQuoteId = $this->checkoutSession->getOapmOrderQuoteId();
        // if not oapm confirmation order
        if ($oapmOrderQuoteId !== $observer->getQuote()->getId()) {
            return;
        }

        $quote = $observer->getQuote();
        $order = $observer->getOrder();
        $oapmOrderData = $this->orderRepository->get( $this->checkoutSession->getOapmOrderId() );

        // remove order payment entity remaining from the initial oapm order
        if( $oapmOrderData->getPayment() ) {
            $this->orderPaymentRepository->delete( $oapmOrderData->getPayment() );
        }
        // remove billing address entity remaining from the initial oapm order
        if( $oapmOrderData->getBillingAddress() ) {
            $this->orderAddressRepository->delete( $oapmOrderData->getBillingAddress() );
        }

        if( $quote->getPayment() )
            $order->setPayment( $this->quotePaymentToOrderPayment->convert( $quote->getPayment() ) );

        $order->setId($oapmOrderData->getEntityId());

        // remove new shipping address because by default only old shipping data is valid
        // user is not allowed to edit shipping details
        if( $order->getShippingAddress() ) {
            $this->orderAddressRepository->delete( $order->getShippingAddress() );
        }

        // solution to double items in oapm orders
        $order->setItems([]);
    }
}
