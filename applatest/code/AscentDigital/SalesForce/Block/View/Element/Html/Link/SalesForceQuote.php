<?php
namespace AscentDigital\SalesForce\Block\View\Element\Html\Link;

use Magento\Customer\Model\Session as CustomerSession;

class SalesForceQuote extends \Magento\Framework\View\Element\Html\Link\Current
{
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
        CustomerSession $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->_defaultPath = $defaultPath;
        $this->customerSession = $customerSession;
    }

    public function toHtml()
    {
        if($this->customerSession->getCustomer()->getData('Customer_Type') == 1) {
            return parent::toHtml();
        }
        return '';
    }  
}