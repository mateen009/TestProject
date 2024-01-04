<?php
namespace Mobility\QuoteRequest\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * QuoteRequest module configuration
 */
class Config implements ConfigInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return (bool) $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function requestedEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            ConfigInterface::XML_PATH_EMAIL_REQUESTED_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function approvedEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            ConfigInterface::XML_PATH_EMAIL_APPROVED_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function rejectedEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            ConfigInterface::XML_PATH_EMAIL_REJECTED_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function emailSender()
    {
        return $this->scopeConfig->getValue(
            ConfigInterface::XML_PATH_EMAIL_SENDER,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function emailRecipient()
    {
        return $this->scopeConfig->getValue(
            ConfigInterface::XML_PATH_EMAIL_RECIPIENT,
            ScopeInterface::SCOPE_STORE
        );
    }
}
