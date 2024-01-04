<?php 
namespace AscentDigital\Reports\Controller\Adminhtml\Grid;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
class OrderReportByInventory extends Action
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
        $resultPage->getConfig()->getTitle()->prepend(__('Order Report By Inventory'));
        $resultPage->addBreadcrumb(__('Reports'), __('Order Report By Inventory'));
        $this->_addContent($this->_view->getLayout()->createBlock('AscentDigital\Reports\Block\Adminhtml\Grid\OrderReportByInventory'));
        $this->_view->renderLayout();
    }
    protected function _isAllowed()
    {
        return true;
    }
}