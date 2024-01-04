<?php
namespace Mobility\QuoteRequest\Block\View\Element\Html\Link;

use Magento\Customer\Model\Session as CustomerSession;

class OrderApprovals extends \Magento\Framework\View\Element\Html\Link\Current
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
        if($this->customerSession->getCustomerType() == 3 ||
        $this->customerSession->getCustomerType() == 4 ||
        $this->customerSession->getCustomerType() == 5) {
            return parent::toHtml();
        }
        return '';
    }
}

