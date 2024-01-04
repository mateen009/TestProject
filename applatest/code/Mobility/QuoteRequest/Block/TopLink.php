<?php
declare(strict_types=1);

namespace Mobility\QuoteRequest\Block;

use Magento\Customer\Block\Account\SortLinkInterface;
use Magento\Framework\View\Element\Html\Link;

class TopLink extends Link implements SortLinkInterface
{
    protected $_storeManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    public function toHtml(): string
    {
        // Website Id
        $currentWebsiteId = $this->_storeManager->getStore()->getWebsiteId();
        if ($currentWebsiteId == '3') {
            return parent::toHtml();
        }
        return '';

    }

    public function getPath(): string
    {
        if (!$this->getData('path')) {
            $this->setData('path', 'quote/account/request/');
        }

        return $this->getData('path');
    }

    public function getLabel(): string
    {
        return (string)__('My Quote Requests');
    }

    public function getSortOrder(): int
    {
        return (int)$this->getData(self::SORT_ORDER);
    }
}
