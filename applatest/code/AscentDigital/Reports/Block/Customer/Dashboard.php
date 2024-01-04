<?php

namespace AscentDigital\Reports\Block\Customer;

use Magento\Customer\Block\Account\SortLinkInterface;
use Magento\Framework\View\Element\Html\Link\Current;

class Dashboard extends \Magento\Framework\View\Element\Html\Link\Current implements \Magento\Customer\Block\Account\SortLinkInterface
{
    protected $_customerSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
     ) {
         $this->_customerSession = $customerSession;
         $this->storeManager = $storeManager;
         parent::__construct($context, $defaultPath, $data);
     }

    protected function _toHtml()
    {
        $currentWebsiteId = $this->storeManager->getStore()->getWebsiteId();
        $responseHtml = null; //  need to return at-least null
        // $responseHtml = parent::_toHtml();
        if ($this->_customerSession->isLoggedIn()) {

            $_storeId = $this->storeManager->getStore()->getId();

            //$customerGroup = $this->_customerSession->getCustomer()->getGroupId(); //Current customer groupID

            if (
                $currentWebsiteId == '3' &&
                (
                    $this->_customerSession->getCustomerType() == 3 ||
                    $this->_customerSession->getCustomerType() == 4 ||
                    $this->_customerSession->getCustomerType() == 5
                )
            ) {
                $responseHtml = parent::_toHtml(); //Return link html
            }
        }
        return $responseHtml;
    }
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}