<?php

namespace Mobility\QuoteRequest\Controller\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NotFoundException;
use Mobility\QuoteRequest\Model\QuoteRequestFactory;
use Magento\Framework\Controller\Result\Redirect;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var \Mobility\QuoteRequest\Model\QuoteRequestFactory
     */
    protected $quoteRequestFactory;

    protected $_storeManager;
 
    /**
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        Context $context,
        QuoteRequestFactory $quoteRequestFactory,
        CheckoutSession $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->quoteRequestFactory = $quoteRequestFactory;
        $this->checkoutSession = $checkoutSession;
        $this->_storeManager = $storeManager;
    }

    /**
     * Show Quote page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        //Website Id
        $currentWebsiteId = $this->_storeManager->getStore()->getWebsiteId();
        if ($currentWebsiteId == '1'){
            return $this->resultRedirectFactory->create()->setPath('checkout');
        }
        $quote = $this->checkoutSession->getQuote();
        $requestQuoteCollection = $this->quoteRequestFactory->create()->getCollection();
        $requestQuoteCollection->addFieldToFilter('quote_id', $quote->getId())->getFirstItem();
        $quoteData = $requestQuoteCollection->getFirstItem();
        if ($quoteData->getId()) {
            $quote->setFormQuoteId($quoteData->getId());
            $quote->save();
        }
        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }

    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     * @throws NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        // if (!$this->customerSession->isLoggedIn()) {
        //     throw new NotFoundException(__('Page not found.'));
        // }
        return parent::dispatch($request);
    }
}
