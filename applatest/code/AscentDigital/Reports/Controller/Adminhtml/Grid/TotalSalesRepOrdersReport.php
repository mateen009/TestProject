<?php 
namespace AscentDigital\Reports\Controller\Adminhtml\Grid;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
class TotalSalesRepOrdersReport extends Action
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
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Total SalesRep Orders Report'));
        $resultPage->addBreadcrumb(__('Reports'), __('Total SalesRep Orders Report'));
        return $resultPage;
        // $this->_view->loadLayout();
        // $resultPage = $this->resultPageFactory->create();
        // $resultPage->getConfig()->getTitle()->prepend(__('Total SalesRep Orders Report'));
        // $resultPage->addBreadcrumb(__('Reports'), __('Total SalesRep Orders Report'));
        // $this->_addContent($this->_view->getLayout()->createBlock('AscentDigital\Reports\Block\Adminhtml\Grid\TotalSalesRepOrdersReport'));
        // $this->_view->renderLayout();
    }
    protected function _isAllowed()
    {
        return true;
    }
}