<?php

namespace AscentDigital\Reports\Helper\AdminReports;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;

class DueOrdersReportCsv extends AbstractHelper
{
    public function __construct(
        Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->fileFactory = $fileFactory;
        parent::__construct($context);
    }
    /**
     * CSV Create and Download
     *
     * @return ResponseInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function generateCSV()
    {
        $filepath = 'export/due_back_orders_report.csv';
        $this->directory->create('export');
        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();

        $header = ['SO #', 'Email', 'Agency Name','Sku', 'Item Name', 'Due Date', 'Return Status'];
        $stream->writeCsv($header);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $ordersBlock = $objectManager->get('AscentDigital\Reports\Block\Adminhtml\Reports\DueOrders');
        $collection= $ordersBlock->getOrderCollection();

        foreach ($collection as $order) {
            $customerData = [];
            $customerData[] = $order->getNsSoNumber();
            $customerData[] = $order->getCustomerEmail();
            $customerData[] = $order->getAgencyName();
            $customerData[] = $order->getSku();
            $customerData[] = $order->getName();
            $customerData[] = date('M d, Y', strtotime($order->getDueDate()));
            $customerData[] = $order->getReturnStatus();
            $stream->writeCsv($customerData);
        }
        // $filepath = 'export/order_inventory_report_csv.csv';
        // $content = [];
        // $content['type'] = 'filename'; // must keep filename
        // $content['value'] = $filepath;
        // // $content['rm'] = '1'; //remove csv from var folder

        // $csvfilename = 'order inventory Report.csv';
        // return $this->fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
    }

}
