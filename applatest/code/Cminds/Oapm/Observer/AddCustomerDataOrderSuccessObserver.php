<?php

namespace Cminds\Oapm\Observer;

/**
 * Class AddCustomerDataOrderSuccessObserver
 * @package Cminds\Oapm\Observer
 */
class AddCustomerDataOrderSuccessObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Magento\Checkout\Model\Session $checkoutSession
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface $_customerRepositoryInterface
     */
    protected $_customerRepositoryInterface;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->layout = $layout;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * $lastOrderId = $observer->getData('order_ids')[0]
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $this->checkoutSession->getLastRealOrder();
        $customerId = $order->getCustomerId();
        $isApproval1Required = 0;
        $isApproval2Required = 0;
        $isCustomerApprovalRequired = 0;
        if ($customerId != '') {
            $customer = $this->_customerRepositoryInterface->getById($customerId);

            $isApproval1 = $customer->getCustomAttribute('Approval_1_ID');
            if ($isApproval1) {
                $isApproval1Required = (int)$isApproval1->getValue();
            }

            $isApproval2 = $customer->getCustomAttribute('Approval_2_ID');
            if ($isApproval2) {
                $isApproval2Required = (int)$isApproval2->getValue();
            }

            $isCustomerApproval = $customer->getCustomAttribute('customer_approval');
            if ($isCustomerApproval) {
                $isCustomerApprovalRequired = (int)$isCustomerApproval->getValue();
            }
        }
        $isApprovalRequired = false;
        if ($isApproval1Required || $isApproval2Required || $isCustomerApprovalRequired) {
            $isApprovalRequired = true;
        }
        $payment = $order->getPayment();

        if ($payment->getMethod() !== \Cminds\Oapm\Model\Payment\Oapm::METHOD_CODE) {
            return;
        }

        $this->layout->getBlock('checkout.success')
            ->setTemplate('Cminds_Oapm::checkout/success.phtml')
            ->setIsApprovalRequired($isApprovalRequired)
            ->setPayerEmail($payment->getAdditionalInformation('recipient_email'));
        // add thank you title
        if ($isApprovalRequired) {

            $this->layout->getBlock('page.main.title')
                ->setPageTitle(__('The order has been created and pending approval!'));
        } else {
            $this->layout->getBlock('page.main.title')
                ->setPageTitle(__('Thank you for your order!'));
        }
    }
}
