<?php
declare(strict_types=1);

namespace Mobility\QuoteRequest\Block;

use Magento\Customer\Block\Account\SortLinkInterface;
use Magento\Framework\View\Element\Html\Link;
use Magento\Customer\Model\Session as CustomerSession;

class OrderTopLink extends Link implements SortLinkInterface
{
    protected $_storeManager;
    protected $customerSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CustomerSession $customerSession,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    public function toHtml(): string
    {
        // Website Id
        $currentWebsiteId = $this->_storeManager->getStore()->getWebsiteId();
        if ($currentWebsiteId == '3' && 
        ($this->customerSession->getCustomerType() == 3 ||
        $this->customerSession->getCustomerType() == 4 ||
        $this->customerSession->getCustomerType() == 5)
        ) {
            return parent::toHtml();
        }
        return '';

    }

    public function getPath(): string
    {
        if (!$this->getData('path')) {
            $this->setData('path', 'quote/account/orderapprovals/');
        }

        return $this->getData('path');
    }

    public function getLabel(): string
    {
        return (string)__('My Order Approvals');
    }

    public function getSortOrder(): int
    {
        return (int)$this->getData(self::SORT_ORDER);
    }
}
