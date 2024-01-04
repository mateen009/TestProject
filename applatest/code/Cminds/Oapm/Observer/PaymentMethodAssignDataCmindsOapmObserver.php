<?php
namespace Cminds\Oapm\Observer;

/**
 * Class PaymentMethodAssignDataCmindsOapmObserver
 * @package Cminds\Oapm\Observer
 */
class PaymentMethodAssignDataCmindsOapmObserver extends \Magento\Payment\Observer\AbstractDataAssignObserver
{
    /**
     * @var \Cminds\Oapm\Helper\Config
     */
    protected $helperConfig;

    /**
     * @param \Cminds\Oapm\Helper\Config $helperConfig
     */
    public function __construct(
        \Cminds\Oapm\Helper\Config $helperConfig
    ) {
        $this->helperConfig = $helperConfig;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $paymentMethod = $this->readMethodArgument($observer);

        $data = $this->readDataArgument($observer);
        $additionalData = $data->getData(\Magento\Quote\Api\Data\PaymentInterface::KEY_ADDITIONAL_DATA);
        if (! is_array($additionalData)) {
            return;
        }

        $payment = $observer->getPaymentModel();
        if (! $payment instanceof \Magento\Payment\Model\InfoInterface) {
            $payment = $paymentMethod->getInfoInstance();
        }
        if (! $payment instanceof \Magento\Payment\Model\InfoInterface) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Payment model does not provided.'));
        }

        if ($this->helperConfig->getConfigData('approver') == \Cminds\Oapm\Model\Config\Source\Approver::APPROVER_CUSTOMER) {
            $additionalInformation['recipient_name'] = $additionalData['recipient_name'];
            $additionalInformation['recipient_email'] = $additionalData['recipient_email'];
        } else {
            $additionalInformation['recipient_name'] = $this->helperConfig->getAdminSenderName();
            $additionalInformation['recipient_email'] = $this->helperConfig->getAdminSenderEmail();
        }
        $additionalInformation['recipient_note'] = $additionalData['recipient_note'];

        $managerEmail = $this->helperConfig->checkCustomerGroupManagerEmail();
        if (!empty($this->helperConfig->useGroupManagerEmail()) && !empty($managerEmail)) {
            $additionalInformation['recipient_name'] = 'Manager';
            $additionalInformation['recipient_email'] = $managerEmail;
        }

        $payment->setAdditionalInformation('recipient_name', $additionalInformation['recipient_name']);
        $payment->setAdditionalInformation('recipient_email', $additionalInformation['recipient_email']);
        $payment->setAdditionalInformation('recipient_note', $additionalInformation['recipient_note']);
    }
}
