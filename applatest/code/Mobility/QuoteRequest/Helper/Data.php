<?php

namespace Mobility\QuoteRequest\Helper;

use Mobility\QuoteRequest\Model\ConfigInterface;

/**
 * QuoteRequest base helper
 *
 * @see \Mobility\QuoteRequest\Model\ConfigInterface
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_ENABLED = ConfigInterface::XML_PATH_ENABLED;
    protected $_storeManager;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Check if enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if website is first net
     *
     * @return bool
     */
    public function isFirstNet()
    {
        //Website Id
        $currentWebsiteId = $this->_storeManager->getStore()->getWebsiteId();
        if ($currentWebsiteId == '3'){
            return true;
        }
        return false;
    }

    /**
     * Check if website is Ariba net
     *
     * @return bool
     */
    public function isAriba()
    {
        //Website Id
        $currentWebsiteId = $this->_storeManager->getStore()->getWebsiteId();
        if ($currentWebsiteId == '4'){
            return true;
        }
        return false;
    }

    /**
     * Check if website is Mobility net
     *
     * @return bool
     */
    public function isMobility()
    {
        //Website Id
        $currentWebsiteId = $this->_storeManager->getStore()->getWebsiteId();
        if ($currentWebsiteId == '1'){
            return true;
        }
        return false;
    }
}
