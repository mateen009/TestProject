<?php

namespace Mobility\QuoteRequest\Block\View\Element\Html\Link;

use Magento\Customer\Model\Session as CustomerSession;

class QuoteApprovals extends \Magento\Framework\View\Element\Html\Link\Current
{
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
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->_defaultPath = $defaultPath;
        $this->_storeManager = $storeManager;
    }

    public function toHtml()
    {
        // Website Id
        $currentWebsiteId = $this->_storeManager->getStore()->getWebsiteId();
        if ($currentWebsiteId == '3') {
            return parent::toHtml();
        }
        return '';
    }
}
