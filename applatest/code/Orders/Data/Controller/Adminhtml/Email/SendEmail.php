<?php

namespace Orders\Data\Controller\Adminhtml\Email;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\App\Action;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\ObjectManager;

class SendEmail extends Action
{
    protected $quoteFactory;
    protected $orderinterface;
    protected $request;

    public function __construct(
        Action\Context $context,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Sales\Api\Data\OrderInterface $orderinterface,
        \Magento\Framework\App\Request\Http $request
    ) {
        parent::__construct($context);
        $this->orderinterface = $orderinterface;
        $this->request = $request;
        $this->quoteFactory = $quoteFactory;
    }

    public function execute()
    {
        $objectManager = ObjectManager::getInstance();
        $this->quoteRequestFactory = $objectManager->create('Mobility\QuoteRequest\Model\QuoteRequestFactory');
        $this->_customerRepositoryInterface = $objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
        $this->mailHelper = $objectManager->create('Cminds\Oapm\Helper\Data');

        $customerEmail = $this->getRequest()->getPostValue('emailfield1value');
        $orderid = $this->getRequest()->getPostValue('orderid');
        $order = $this->orderinterface->load($orderid);
        //customer
        $customer = $this->_customerRepositoryInterface->getById($order->getCustomerId());
        // customer telephone number 
        $isCustomerPhoneNumber = $customer->getCustomAttribute('customer_phone');
        $customer_phone = '';
        if ($isCustomerPhoneNumber) {
            $customer_phone = $isCustomerPhoneNumber->getValue();
        }
        //customer token
        $customerToken = $order->getCustomerApprovalToken();

        //getting form quote
        $quote_id = $order->getQuoteId();
        $formQuoteCollection = $this->quoteRequestFactory->create()->getCollection();
        $formQuote = $formQuoteCollection->addFieldToFilter('quote_id', $quote_id)->getFirstItem();
        $recipientData = array(
            'creator_name' => $order->getCustomerName(),
            'order' => $order,
            'quote' => $formQuote,
            'customer' => $customer,
            'customer_phone' => $customer_phone
        );
        $this->mailHelper->sendOrderPlacedCustomerNotification($recipientData, $customerToken, ['email' => $customerEmail, 'name' => $formQuote->getCustomerName()]);
        return ;
    }
}
