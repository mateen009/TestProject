<?php 
namespace AscentDigital\Reports\Controller\Adminhtml\Grid;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
class InventoryListing extends Action
{
    protected $resultPageFactory;
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    public function execute()
    {
        $this->_view->loadLayout();
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Inventory Listing'));
        $resultPage->addBreadcrumb(__('Reports'), __('Inventory Listing'));
        $this->_addContent($this->_view->getLayout()->createBlock('AscentDigital\Reports\Block\Adminhtml\Grid\InventoryListing'));
        $this->_view->renderLayout();
    }
    protected function _isAllowed()
    {
        return true;
    }
}