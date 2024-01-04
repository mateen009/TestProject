<?php
namespace Mobility\QuoteRequest\Block\Quote\Account;

use Magento\Framework\View\Element\Template;
use Mobility\QuoteRequest\Api\Data\QuoteRequestInterface;
use Mobility\QuoteRequest\Api\QuoteRequestRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Main quote Approval block
 */
class Approval extends Template
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
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @param Template\Context $context
     * @param QuoteRequestRepositoryInterface $quoteRequestRepository
     * @param CheckoutSession $checkoutSession
     * @param CustomerSession $customerSession
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        QuoteRequestRepositoryInterface $quoteRequestRepository,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->quoteRequestRepository = $quoteRequestRepository;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
    }

    /**
     * Returns action url for quote form
     *
     * @return string
     */
    public function getStatusAction($id, $status)
    {
        return $this->getUrl('quote/status/update', ['_secure' => true, 'id' => $id, 'status' => $status]);
    }

    /**
     * Returns custmomer type
     *
     * @return int
     */
    public function getCustomerType()
    {
        return (int) $this->customerSession->getCustomerType();
    }

    /**
     * Fetch Customer Quote Request List
     *
     * @return mixed
     */
    public function getCustomerQuoteRequestList() 
    {
        $quote = $this->checkoutSession->getQuote();
        if($this->customerSession->getCustomerType() == 3) {
            $requestQuoteCollection = $this->quoteRequestRepository->getCustomerQuoteApproval1List($quote->getCustomerId(), ['requested', 'approved']);
        } else if($this->customerSession->getCustomerType() == 4) {
            $requestQuoteCollection = $this->quoteRequestRepository->getCustomerQuoteApproval2List($quote->getCustomerId(), ['requested', 'approved']);
        } else {
            $requestQuoteCollection = $this->quoteRequestRepository->getCustomerQuoteApproval1List(0, ['requested', 'approved']);
        }
        
        return $requestQuoteCollection;
    }
}
