<?php
namespace Cminds\Oapm\Plugin\Checkout\Model;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Cminds\Oapm\Helper\Config as HelperConfig;
use Magento\Customer\Model\GroupRegistry;

class DefaultConfigProvider
{
    /**
     * @var CheckoutSession $checkoutSession
     */
    protected $checkoutSession;

    /**
     * @var CustomerSession $customerSession
     */
    protected $customerSession;

    /**
     * @var QuoteIdMaskFactory $quoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var HelperConfig
     */
    protected $helperConfig;

    /**
     * @param CheckoutSession $checkoutSession
     * @param CustomerSession $customerSession
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param HelperConfig $helperConfig,
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        OrderRepositoryInterface $orderRepository,
        HelperConfig $helperConfig
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->orderRepository = $orderRepository;
        $this->helperConfig = $helperConfig;
    }

    /**
     * @param CheckoutSession $subject
     * @param array $configData
     */
    public function afterGetConfig(
        \Magento\Checkout\Model\DefaultConfigProvider $subject, $configData
    ) {
        // if module disabled in admin panel
        if (! $this->helperConfig->getConfigData('active')) {
            return $configData;
        }

        // if it's the oapm order confirmation case
        if(
            $this->checkoutSession->getOapmOrderQuoteId()
            && $this->checkoutSession->getQuote()
        ) {
            // add email data to frontend
            $configData['oapm']['customerEmail'] = $this->checkoutSession
                ->getQuote()
                ->getShippingAddress()
                ->getEmail();
            // fix for an issue wen oapm order was created by a logged in account and the link
            // is opened in a new window, where customer is not logged in
            if( !$this->customerSession->isLoggedIn() ){
                $quoteIdMask = $this->quoteIdMaskFactory->create();
                $configData['quoteData']['entity_id'] = $quoteIdMask->load(
                        $this->checkoutSession->getQuote()->getId(),
                        'quote_id'
                    )->getMaskedId();
                $configData['quoteData']['checkout_method'] = 'guest';
                $configData['quoteData']['customer_id'] = null;
            }
        }

        if (!empty($this->helperConfig->useGroupManagerEmail())
            && !empty($this->helperConfig->checkCustomerGroupManagerEmail())
        ) {
            $configData['oapm']['hideFields'] = true;
        }

        if((int) $this->helperConfig->getConfigData('approver')
            === \Cminds\Oapm\Model\Config\Source\Approver::APPROVER_ADMIN ){
            $configData['oapm']['hideFields'] = true;
        }

        return $configData;
    }
}