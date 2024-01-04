<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Cpm\UseMyTerm\Observer\Sales;

class OrderPlaceAfter implements \Magento\Framework\Event\ObserverInterface
{

    protected $_customerSession;

    public function __construct    (             
        \Magento\Sales\Model\Order $order,
        \Magento\Customer\Model\Session $customerSession
       ) 
     {        
        $this->order = $order;     
        $this->_customerSession = $customerSession;
     }
    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $order = $observer->getEvent()->getOrder();

        //get user specific title of UserMyTerms payment method
        $useTermsText = '';
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
        // $logger->info('Save Order After:'.$canUseTerms.$useTermsText);

        $order->setData('usertermstitle', $useTermsText);
        $order->save();
    }
}

