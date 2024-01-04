<?php
namespace Cminds\Oapm\Observer;

/**
 * Class PaymentMethodIsActiveObserver
 * @package Cminds\Oapm\Observer
 */
class PaymentMethodIsActiveObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Cminds\Oapm\Helper\Config
     */
    protected $helperConfig;

    /**
     * @var \Magento\Checkout\Model\Session $checkoutSession
     */
    protected $checkoutSession;

    /**
     * @param \Cminds\Oapm\Helper\Config $helperConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Cminds\Oapm\Helper\Config $helperConfig,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->helperConfig = $helperConfig;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Prepare payment methods
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (! $this->helperConfig->getConfigData('active')) {
            return $this;
        }

        if ($this->helperConfig->getConfigData('approver') === \Cminds\Oapm\Model\Config\Source\Approver::APPROVER_CUSTOMER) {
            return $this;
        }

        $result = $observer->getResult();
        $methodInstance = $observer->getMethodInstance();

        // remove oapm payment method in case of oapm confirmation
        if (
            $methodInstance->getCode() === \Cminds\Oapm\Model\Payment\Oapm::METHOD_CODE
            && $this->checkoutSession->getOapmOrderId()
        ) {
            $result->setData('is_available', false);
        }
    }
}
