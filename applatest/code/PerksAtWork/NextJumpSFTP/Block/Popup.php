<?php

namespace PerksAtWork\NextJumpSFTP\Block;

class Popup extends \Magento\Framework\View\Element\Template
{
    protected $customerSession;
    protected $_checkoutSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
    ) {
        $this->customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    public function getCsid()
    {
        $getCheckoutCsid = $this->getCheckoutSession()->getCsid();
        if(isset($getCheckoutCsid)){
            $csid = $getCheckoutCsid;
            return $csid;
        }
        
    }
    public function getCheckoutSession() 
{
    return $this->_checkoutSession;
}
    public function getPopupMessage() 
{
    $messagePopup = $this->getCheckoutSession()->getRefererPopup();
    if($messagePopup){
        
        return "You must login to PerksAtWork.com to access this site";
    }
    else{
        return "You must access this site from your “Perks At Work” account for further purchase";
    }
}
}
