<?php

namespace Mobility\QuoteRequest\Controller\Index;

class NewQuote extends \Magento\Framework\App\Action\Action
{
	public function __construct(
		\Magento\Framework\App\Action\Context $context
	) {
		return parent::__construct($context);
	}

	public function execute()
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		// variable that should be shift to construct 
		
		$resultFactory = $objectManager->create('Magento\Framework\Controller\ResultFactory');
		$checkoutSession = $objectManager->get('Magento\Checkout\Model\Session');
		$quoteFactory = $objectManager->get('Magento\Quote\Model\QuoteFactory');
		$messageManager = $objectManager->create('Magento\Framework\Message\ManagerInterface');
		$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		// variable that should be shift to construct 

		$redirect = $resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
		$baseUrl = $storeManager->getStore()->getBaseUrl();


		try {
			if ($checkoutSession->getQuoteId()) {
				$quoteId = $checkoutSession->getQuoteId();
				$quote = $quoteFactory->create()->load($quoteId);
				$quote->setIsActive(0);
				$quote->save();
				$messageManager->addSuccess(__('The previous quote is saved successfully! Now you can create new.'));
				$redirect->setUrl($baseUrl);
				return $redirect;
			}
		} catch (\Exception $e) {
			$messageManager->addError(__($e->getMessage()));
			$redirect->setUrl($baseUrl . 'quote/');
			return $redirect;
		}
	}
}
