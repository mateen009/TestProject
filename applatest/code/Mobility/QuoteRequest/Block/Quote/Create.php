<?php

namespace Mobility\QuoteRequest\Block\Quote;

use Magento\Framework\View\Element\Template;
use Mobility\QuoteRequest\Api\Data\QuoteRequestInterface;
use Mobility\QuoteRequest\Api\QuoteRequestRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Mobility\QuoteRequest\Model\ConfigInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Main quote Create form block
 */
class Create extends Template
{
    /**
     * @var QuoteRequestRepositoryInterface
     */
    private $quoteRequestRepository;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;
    protected $_addressFactory;

    /**
     * @param Template\Context $context
     * @param QuoteRequestRepositoryInterface $quoteRequestRepository
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        QuoteRequestRepositoryInterface $quoteRequestRepository,
        CheckoutSession $checkoutSession,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->quoteRequestRepository = $quoteRequestRepository;
        $this->checkoutSession = $checkoutSession;
        $this->customerRepository = $customerRepository;
        $this->_addressFactory = $addressFactory;
    }

    public function _prepareLayout()
    {
        $quoteRequest = $this->getCustomerQuoteRequestList();
        if ($quoteRequest->getId()) {
            $this->pageConfig->getTitle()->set(__('Quote: #' . $quoteRequest->getId()));
        } else {
            $this->pageConfig->getTitle()->set(__('Opportunity Form'));
        }

        return parent::_prepareLayout();
    }

    /**
     * Returns action url for quote form
     *
     * @return string
     */
    public function getFormAction()
    {
        return $this->getUrl('quote/index/post', ['_secure' => true]);
    }

    /**
     * Fetch Customer Quote Request List
     *
     * @return mixed
     */
    public function getCustomerQuoteRequestList()
    {
        $quote = $this->checkoutSession->getQuote();
        if ($quote->getId()) {
            $requestQuoteCollection = $this->quoteRequestRepository->getCustomerQuoteRequestList($quote->getCustomerId(), [ConfigInterface::STATUS_REQUESTED, ConfigInterface::STATUS_APPROVED], $quote->getId());

            return $requestQuoteCollection->getFirstItem();
        } else {
            return $this;
        }
    }

    /**
     * get attachment url
     */

    public function getAttachmentUrl()
    {
        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'orders/attachment/';
        return $mediaUrl;
    }

    /**
     * get cart item quantity 
     */

    public function getCartItemQty()
    {
        $quote = $this->checkoutSession->getQuote();
        return $quote->getItemsQty();
    }

    public function getCustomerDefaultShippingAddress()
    {
        $quote = $this->checkoutSession->getQuote();
        $customerId = $quote->getCustomerId();
        $quoteRequest = $this->getCustomerQuoteRequestList();
        if (!empty($quoteRequest->getPreviousDefaultBilling()) && $quoteRequest->getPreviousDefaultBilling() > 0) {
            $baddressId = (int)$quoteRequest->getPreviousDefaultBilling();
            $billingAddress = $this->_addressFactory->create()->load($baddressId);
            $billingAddress->setCustomerId($customerId);
            $billingAddress->setIsDefaultBilling('1');
            $billingAddress->save();
            echo $billingAddress->getId();
            $quoteRequest->setPreviousDefaultBilling('');
            $quoteRequest->save();
        }
        if (!empty($quoteRequest->getPreviousDefaultShipping()) && $quoteRequest->getPreviousDefaultShipping() > 0) {
            $addressId = (int)$quoteRequest->getPreviousDefaultShipping();
            $shippingAddress = $this->_addressFactory->create()->load($addressId);
            $shippingAddress->setCustomerId($customerId);
            $shippingAddress->setIsDefaultShipping('1');
            $shippingAddress->save();
            echo $shippingAddress->getId();
            $quoteRequest->setPreviousDefaultShipping('');
            $quoteRequest->save();
            if ($shippingAddress->getId()) {
                return $shippingAddress;
            } else {
                $customer = $this->customerRepository->getById($customerId);
                $shippingAddressId = $customer->getDefaultShipping();
                return $shippingAddressId;
            }
        } else {
            $customer = $this->customerRepository->getById($customerId);
            $shippingAddressId = $customer->getDefaultShipping();
            if ($shippingAddressId) {
                $shippingAddress = $this->_addressFactory->create()->load($shippingAddressId);
                return $shippingAddress;
            }
            return $shippingAddressId;
        }
    }



    // public function getCustomerDefaultShippingAddress()
    // {
    //     $addressId = $this->isShippingAddress();
    //     die($addressId);
    //     if ($addressId) {
    //         $shippingAddress = $this->_addressFactory->create()->load($addressId);
    //         return $shippingAddress;
    //     }
    //     return $addressId;
    // }
}
