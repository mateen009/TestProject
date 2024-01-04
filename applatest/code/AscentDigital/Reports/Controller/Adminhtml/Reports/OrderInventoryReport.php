<?php

namespace AscentDigital\Reports\Controller\Adminhtml\Reports;

use Magento\Framework\App\Filesystem\DirectoryList;
use AscentDigital\Reports\Helper\AdminReports\OrderInventoryReportCsv;

class OrderInventoryReport extends \Magento\Backend\App\Action
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
        // export_data
        $export_data = $this->request->getParam('export_data');
        if (isset($export_data)) {
            $this->csvHelper->generateCSV();
            $this->gencsv();
        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Order Inventory Report'));

        return $resultPage;
    }

    protected function _isAllowed()
    {
        return true;
    }

    public function gencsv()
    {
        try {
            $filepath = 'export/order_inventory_report_csv.csv';
            $content = [];
            $content['type'] = 'filename'; // must keep filename
            $content['value'] = $filepath;
            $content['rm'] = '1'; //remove csv from var folder

            $csvfilename = 'order inventory Report.csv';
            return $this->fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}
