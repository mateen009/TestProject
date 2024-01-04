<?php

namespace Mobility\QuoteRequest\Controller\Status;

class Activate extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context
    ) {
        return parent::__construct($context);
    }

    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        // variable that should be shift to construct 

        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
        $resultFactory = $objectManager->create('Magento\Framework\Controller\ResultFactory');
        $quoteRequestFactory = $objectManager->get('Mobility\QuoteRequest\Model\QuoteRequestFactory');
        $quoteFactory = $objectManager->get('Magento\Quote\Model\QuoteFactory');
        $messageManager = $objectManager->create('Magento\Framework\Message\ManagerInterface');
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $this->cart = $objectManager->create('Magento\Checkout\Model\Cart');
        $product = $objectManager->create('Magento\Catalog\Model\ProductFactory');
        $this->connection = $objectManager->create('Magento\Framework\App\ResourceConnection')->getConnection();

        $redirect = $resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $baseUrl = $storeManager->getStore()->getBaseUrl();
        $requestQuoteCollection = $quoteRequestFactory->create()->getCollection();
        $id = (int)$this->getRequest()->getParam('id');
        if (isset($id)) {
            try {
                $requestQuoteCollection->addFieldToFilter('id', $id)->addFieldToFilter('quote_id', ['neq' => '0']);
                $quoteData = $requestQuoteCollection->getFirstItem();
                $quoteCustomerId = $quoteData->getCustomerId();
                $quoteId = $quoteData->getQuoteId();
                if ($quoteId) {
                    $quoteFactory = $quoteFactory->create();
                    $quoteCollection = $quoteFactory->getCollection();
                    $customerId = $customerSession->getCustomerId();
                    if ($customerId == $quoteCustomerId) {
                        $carts = $quoteCollection->addFieldToFilter('customer_id', $customerId)->addFieldToFilter('is_active', '1');
                        foreach ($carts as $cart) {
                            $cart->setIsActive('0');
                            $cart->save();
                        }
                        $quote = $quoteFactory->load($quoteId);
                        if (!empty($quoteData->getOrderId())) {
                            // reorder
                            $items = $quote->getAllVisibleItems();

                            foreach ($items as $item) {
                                $productId = $item->getProductId();
                                $_product = $product->create()->load($productId);

                                $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());

                                $info = $options['info_buyRequest'];
                                $request1 = new \Magento\Framework\DataObject();
                                $request1->setData($info);

                                $this->cart->addProduct($_product, $request1);
                            }
                            $this->cart->save();
                            $quoteFormData = (array)$quoteData->getData();
                            unset($quoteFormData['id']);
                            unset($quoteFormData['order_id']);
                            $quoteFormData['quote_id'] = $this->cart->getQuote()->getId();
                            $quoteFormData['created_at'] = date("Y-m-d H:i:s");
                            $quoteFormData['updated_at'] = date("Y-m-d H:i:s");
                            $tableName = 'mobility_quote_request';
                            $this->connection->insert($tableName, $quoteFormData);
                            $messageManager->addSuccess(__('New Quote is created for reorder.'));
                        } else {
                            $quote->setIsActive('1');
                            $quote->save();
                            $messageManager->addSuccess(__('Quote is activated successfully!'));
                        }
                        $redirect->setUrl($baseUrl . 'quote/index/index');
                        return $redirect;
                    } else {
                        $messageManager->addError(__('Some thing went wrong. Please try again!'));
                        $redirect->setUrl($baseUrl . 'quote/account/request');
                        return $redirect;
                    }
                } else {
                    $messageManager->addError(__('This quote have no cart item.'));
                    $redirect->setUrl($baseUrl . 'quote/account/request');
                    return $redirect;
                }
            } catch (\Exception $e) {
                $messageManager->addError(__($e->getMessage()));
                $redirect->setUrl($baseUrl . 'quote/account/request');
                return $redirect;
            }
        }
    }
}
