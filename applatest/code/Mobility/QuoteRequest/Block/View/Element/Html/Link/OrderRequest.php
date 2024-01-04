<?php
namespace Mobility\QuoteRequest\Block\View\Element\Html\Link;

use Magento\Customer\Model\Session as CustomerSession;

class OrderRequest extends \Magento\Framework\View\Element\Html\Link\Current
{
    protected $customerSession;

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
            return parent::toHtml();
    }
}

