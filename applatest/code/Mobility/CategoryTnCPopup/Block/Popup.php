<?php

namespace Mobility\CategoryTnCPopup\Block;

class Popup extends \Magento\Framework\View\Element\Template
{
    protected $customerSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\SessionFactory $customerSession
    ) {
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    public function isLoggedIn()
    {
        $session = $this->customerSession->create();
        $customerType = $session->getCustomerType();
        return $customerType;
    }
}
