<?php
namespace Mobility\ForceCustomerLogin\Block\Customer\Account;

use Magento\Store\Model\StoreManagerInterface;

class Attributes extends \Magento\Framework\View\Element\Template
{   
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();;
    }
}