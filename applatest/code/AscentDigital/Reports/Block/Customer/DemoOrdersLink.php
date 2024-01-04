<?php

namespace AscentDigital\Reports\Block\Customer;

use Magento\Customer\Model\Session as CustomerSession;

class DemoOrdersLink extends \Magento\Framework\View\Element\Html\Link\Current
{
    protected $_storeManager;
    protected $customerSession;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param CustomerSession $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CustomerSession $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->_defaultPath = $defaultPath;
        $this->_storeManager = $storeManager;
        $this->customerSession = $customerSession;
    }

    public function toHtml()
    {
        $currentWebsiteId = $this->_storeManager->getStore()->getWebsiteId();
        if (
            $currentWebsiteId == '3' &&
            (
                $this->customerSession->getCustomerType() == 1 ||
                $this->customerSession->getCustomerType() == 3 ||
                $this->customerSession->getCustomerType() == 4 ||
                $this->customerSession->getCustomerType() == 5
            )
        ) {
            return parent::toHtml();
        }
        return '';
    }
}
