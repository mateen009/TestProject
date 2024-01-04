<?php

namespace AscentDigital\Reports\Controller\Adminhtml\Reports;

use Magento\Framework\App\Filesystem\DirectoryList;
use AscentDigital\Reports\Helper\AdminReports\OrderInventoryReportCsv;

class OrderInventory extends \Magento\Backend\App\Action
{

    protected $resultPageFactory;
    protected $request;
    protected $csvHelper;
    protected $fileFactory;
    protected $logger;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        OrderInventoryReportCsv $csvHelper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->fileFactory = $fileFactory;
        $this->request = $request;
        $this->csvHelper = $csvHelper;
        $this->logger = $logger;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Order Inventory Report'));

        return $resultPage;
    }

    protected function _isAllowed()
    {
        return true;
    }
}

