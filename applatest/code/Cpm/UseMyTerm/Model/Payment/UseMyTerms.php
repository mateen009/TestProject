<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Cpm\UseMyTerm\Model\Payment;

class UseMyTerms extends \Magento\Payment\Model\Method\AbstractMethod
{

    protected $_code = "usemyterms";
    protected $_isOffline = true;
    protected $_customerSession;


    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        parent::__construct(
            $context, $registry, $extensionFactory, $customAttributeFactory, $paymentData, $scopeConfig, $logger,
            $resource, $resourceCollection, $data
        );
        $this->_customerSession = $customerSession;
    }

    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        $canUseTerms = 0;

        if ($this->_customerSession->isLoggedIn()) {
            //$customerId = $this->_customerSession->getCustomer()->getId();
            $canUseTerms = $this->_customerSession->getCustomer()->getUseMyTerms();
        } else {
            // $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/customCheckout.log');
            // $logger = new \Zend_Log();
            // $logger->addWriter($writer);
            // $logger->info('Inside Else');
        }

        if($canUseTerms) {
            return parent::isAvailable($quote);
        } else {
            return false;
        }
    }

    public function getTitle()
    {
        $useTermsText = 'Use My Terms';
        $canUseTerms = $this->_customerSession->getCustomer()->getUseMyTerms();
        $optionId = $this->_customerSession->getCustomer()->getUseYourTermsTitle();
        //$attribute = $this->_customerSession->getCustomer()->getAttributeText('use_your_terms_title');
        $attribute = $this->_customerSession->getCustomer()->getResource()->getAttribute('use_your_terms_title');
        if ($attribute->usesSource()) {
            $useTermsText = $attribute->getSource()->getOptionText($optionId);
        }

        // $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/customCheckout.log');
        // $logger = new \Zend_Log();
        // $logger->addWriter($writer);
        // $logger->info('Title:'.$canUseTerms.$optionText);
        return $useTermsText;
    }
}

