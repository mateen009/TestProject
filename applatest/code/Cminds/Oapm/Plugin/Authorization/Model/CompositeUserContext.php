<?php
namespace Cminds\Oapm\Plugin\Authorization\Model;

use Magento\Authorization\Model\UserContextInterface;
use Cminds\Oapm\Helper\Config as OapmConfig;
use Cminds\Oapm\Model\OrderFactory as OapmOrderFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;

class CompositeUserContext
{
    /**
     * @var OapmConfig
     */
    protected $helperConfig;

    /**
     * @var OapmOrderFactory
     */
    protected $oapmOrderFactory;

    /**
     * Quote repository.
     *
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var CheckoutSession $checkoutSession
     */
    protected $checkoutSession;

    /**
     * @var CustomerSession $customerSession
     */
    protected $customerSession;

    /**
     * @param OapmConfig $helperConfig,
     * @param OapmOrderFactory $oapmOrderFactory,
     * @param CartRepositoryInterface $quoteRepository
     * @param CheckoutSession $checkoutSession
     * @param CustomerSession $customerSession
     */
    public function __construct(
        OapmConfig $helperConfig,
        OapmOrderFactory $oapmOrderFactory,
        CartRepositoryInterface $quoteRepository,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession
    ) {
        $this->helperConfig = $helperConfig;
        $this->oapmOrderFactory = $oapmOrderFactory;
        $this->quoteRepository = $quoteRepository;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
    }

    /**
     * @param \Magento\Authorization\Model\CompositeUserContext $subject
     * @param UserContextInterface|bool
     * @return UserContextInterface|bool
     */
    public function afterGetUserType(
        \Magento\Authorization\Model\CompositeUserContext $subject,
        $result
    ) {

        // if module enabled in config
        if($this->helperConfig->isEnabled()){
            $oapmOrderId = $this->checkoutSession->getData('oapm_order_id');

            if($oapmOrderId) {
                $addressData = false;
                if(
                    // all cases when order was created by a logged customer
                    0 === (int) $this->checkoutSession->getOapmQuoteIsGuest()
                    || (
                        // if order is finalized by a logged customer and was created by a guest
                        1 === (int) $this->checkoutSession->getOapmQuoteIsGuest()
                        && $this->customerSession->isLoggedIn()
                    )
                ){
                    // check data, if valid hash passed
                    $oapmOrderData = $this->oapmOrderFactory->create()->loadByOrderId($oapmOrderId);
                    // check if order entry exists and has corresponding status
                    if (!is_null($oapmOrderData->getOrderId())
                        && (
                            (int) $oapmOrderData->getStatus() === \Cminds\Oapm\Model\Order::STATUS_ACTIVE
                            || (int) $oapmOrderData->getStatus() === \Cminds\Oapm\Model\Order::STATUS_FINALIZED
                        )
                    ) {
                        // $result = UserContextInterface::USER_TYPE_INTEGRATION;
                        $result = UserContextInterface::USER_TYPE_CUSTOMER;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param \Magento\Authorization\Model\CompositeUserContext $subject
     * @param int|null
     * @return int|null
     */
    public function afterGetUserId(
        \Magento\Authorization\Model\CompositeUserContext $subject,
        $result
    ) {
        // if module enabled in config
        if($this->helperConfig->isEnabled()){
            $oapmQuoteId = $this->checkoutSession->getData('oapm_order_quote_id');
            if(
                $oapmQuoteId
                && 0 === (int) $this->checkoutSession->getOapmQuoteIsGuest()
                || (
                    1 === (int) $this->checkoutSession->getOapmQuoteIsGuest()
                    && $this->customerSession->isLoggedIn()
                )
            ){
                $quoteData = $this->quoteRepository->get($oapmQuoteId);
                $result = $quoteData->getCustomerId();
            }
        }
        return $result;
    }
}
