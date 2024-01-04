<?php

namespace AscentDigital\SalesForce\Controller\Index;

use Magento\Framework\App\ObjectManager;


/**
 * Approved Controller
 * 
 * approve order status
 */
class AddQuote extends \Magento\Framework\App\Action\Action
{
    protected $resultFactory;
    protected $messageManager;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        return parent::__construct($context);
        $this->resultFactory = $resultFactory;
        $this->messageManager = $messageManager;
    }

    public function execute()
    {
        $objectManager = ObjectManager::getInstance();
        $this->storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $this->salesForceFactory = $objectManager->get('\AscentDigital\SalesForce\Model\SalesForceFactory');
        $this->connection = $objectManager->create('Magento\Framework\App\ResourceConnection')->getConnection();
        $quoteFactory = $objectManager->get('Magento\Quote\Model\QuoteFactory');
        $customerSession = $objectManager->create('Magento\Customer\Model\Session');
        $checkoutSession = $objectManager->create('Magento\Checkout\Model\Session');
        $quoteRequestFactory = $objectManager->create('Mobility\QuoteRequest\Model\QuoteRequestFactory');
        $this->_customerRepository = $objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
        $MtelId = $this->getRequest()->getParam('mtel_id');
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);

        // echo $baseUrl.'<br>';
        // echo $customerSession->getData().'<br>';die;
        if ($customerSession->getCustomer()->getData('Customer_Type')  != 1) {
            $this->messageManager->AddError(__('Please, login as Sales Rep to continue.'));
            $redirect->setUrl($baseUrl . 'salesforce/index/quotedata');
            return $redirect;
        }

        if (isset($MtelId)) {
            try {
                $collection = $this->salesForceFactory->create()->getCollection();
                $quoteData = $collection->addFieldToFilter('tracking_no', $MtelId)->addFieldToFilter('status', ['neq' => 'converted'])->getFirstItem();
                if ($checkoutSession->getQuoteId()) {
                    $requestQuoteCollection = $quoteRequestFactory->create()->getCollection();
                    $requestQuoteCollection->addFieldToFilter('quote_id', $checkoutSession->getQuoteId())->getFirstItem();
                    if ($requestQuoteCollection->getData()) {
                        $quoteFactoryCreate = $quoteFactory->create();
                        $quoteCollection = $quoteFactoryCreate->getCollection();
                        $carts = $quoteCollection->addFieldToFilter('customer_id', $customerSession->getCustomerId())->addFieldToFilter('is_active', '1');
                        foreach ($carts as $cart) {
                            $cart->setIsActive('0');
                            $cart->save();
                        }
                        // new empty cart creating
                        $this->createCart($customerSession, $this->_customerRepository, $quoteFactory, $this->storeManager, $quoteData);

                        $redirect->setUrl($baseUrl . 'quote/index/index'); //Please, add items to cart and continue.
                        return $redirect;
                    } else {
                        // form quote creating
                        $this->createQuoteForm($quoteData, $checkoutSession->getQuoteId(), $customerSession, $this->connection);
                        $redirect->setUrl($baseUrl . 'quote/index/index'); //Please, add items to cart and continue.
                        return $redirect;
                    }
                } else {
                    // new empty cart creating
                    $this->createCart($customerSession, $this->_customerRepository, $quoteFactory, $this->storeManager, $quoteData);
                    $redirect->setUrl($baseUrl . 'quote/index/index'); //Please, add items to cart and continue.
                    return $redirect;
                }
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
                $redirect->setUrl($baseUrl . 'quote/index/index');
                return $redirect;
            }
        } else {
            $this->messageManager->AddError(__('Something went worong. Please try again!'));
            $redirect->setUrl($baseUrl . 'quote/index/index');
            return $redirect;
        }
    }

    public function createCart($customerSession, $customerRepository, $quoteFactory, $storeManager, $quoteData)
    {
        $customer   = $customerRepository->getById($customerSession->getCustomerId());
        $quote      = $quoteFactory->create();
        $quote->setStoreId($storeManager->getStore()->getId());
        $quote->assignCustomer($customer);
        $quote->save();
        // form quote creating
        $this->createQuoteForm($quoteData, $quote->getId(), $customerSession, $this->connection);
    }

    public function createQuoteForm($quoteData, $quoteId, $customerSession, $connection)
    {
        $quoteFormData = (array)$quoteData->getData();
        $quoteFormData['quote_id'] = $quoteId;
        $quoteFormData['customer_id'] = $customerSession->getCustomerId();
        $quoteFormData['primary'] = $quoteData->getFirstNetType()?$quoteData->getFirstNetType():'primary';
        $quoteFormData['created_at'] = date("Y-m-d H:i:s");
        $quoteFormData['updated_at'] = date("Y-m-d H:i:s");
        unset($quoteFormData['id']);
        unset($quoteFormData['opp_tracking_no']);
        unset($quoteFormData['account_name']);
        unset($quoteFormData['first_net_type']);
        unset($quoteFormData['all_rate_plan_quantities2']);
        unset($quoteFormData['close_date']);
        unset($quoteFormData['tracking_no']);
        unset($quoteFormData['mtel_id']);
        unset($quoteFormData['sf_id']);
        // echo "<pre>";print_r($quoteFormData);die;
        $tableName = 'mobility_quote_request';
        $connection->insert($tableName, $quoteFormData);
        $quoteData->setStatus('converted');
        $quoteData->save();
        $this->messageManager->addSuccess(__('New quote created successfully.'));
    }
}
