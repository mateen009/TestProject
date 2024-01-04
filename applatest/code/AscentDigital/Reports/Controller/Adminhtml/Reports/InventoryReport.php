<?php

namespace AscentDigital\Reports\Controller\Adminhtml\Reports;

class InventoryReport extends \Magento\Backend\App\Action
{
    
    protected $resultPageFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Inventory Report'));

        return $resultPage;
    }
    
    protected function _isAllowed()
    {
        return true;
    }
}
