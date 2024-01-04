<?php
namespace Magenest\RentalSystem\Block\View\Element\Html\Link;

use Magento\Store\Model\StoreManagerInterface;

class MyDemoOrders extends \Magento\Framework\View\Element\Html\Link\Current
{
    protected $customerSession;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param CustomerSession $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->_defaultPath = $defaultPath;
        $this->storeManager = $storeManager;
        }

    public function toHtml()
    {
        if($this->storeManager->getStore()->getId() == 2) {
            return parent::toHtml();
        }
        return '';
    }  
}