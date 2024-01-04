<?php
namespace AscentDigital\Reports\Block\View\Element\Html\Link;

use Magento\Customer\Model\Session as CustomerSession;

class SalesReport extends \Magento\Framework\View\Element\Html\Link\Current
{
    protected $customerSession;
    protected $_storeManager;
    
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
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        CustomerSession $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->_storeManager = $storeManager;
        $this->_defaultPath = $defaultPath;
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



