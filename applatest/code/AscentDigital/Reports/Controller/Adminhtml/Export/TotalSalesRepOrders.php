<?php

namespace AscentDigital\Reports\Controller\Adminhtml\Export;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;

class TotalSalesRepOrders extends Action
{
    protected $resultPageFactory;
    protected $fileFactory;
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        FileFactory $fileFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->fileFactory = $fileFactory;
    }
    public function execute()
    {
        $fileName = 'totalsalesreporders.csv';
        $grid = $this->_view->getLayout()->createBlock('AscentDigital\Reports\Block\Adminhtml\Grid\TotalSalesRepOrdersReport');
        $this->fileFactory->create($fileName, $grid->getCsvFile(), DirectoryList::VAR_DIR);
    }
    protected function _isAllowed()
    {
        return true;
    }

    //     public function exportCsvAction()
    // {
    //     $fileName = 'mygrid.csv';
    //     $grid = $this->_view->getLayout()->createBlock('My\Block\Adminhtml\MyGrid');
    //     $this->_fileFactory->create($fileName, $grid->getCsvFile(), DirectoryList::VAR_DIR);
    // }
}
