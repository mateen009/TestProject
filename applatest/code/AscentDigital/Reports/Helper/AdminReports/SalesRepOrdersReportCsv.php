<?php

namespace AscentDigital\Reports\Helper\AdminReports;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;

class SalesRepOrdersReportCsv extends AbstractHelper
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
        $filepath = 'export/sales_rep_orders_report_csv.csv';
        $this->directory->create('export');
        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();

        $header = ['SO #', 'Email', 'Agency Name', 'Sku', 'Item Name', 'Date', 'Status', 'Returned'];
        $stream->writeCsv($header);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $ordersBlock = $objectManager->get('AscentDigital\Reports\Block\Adminhtml\Reports\SalesRepOrders');
        $collection= $ordersBlock->getOrderCollection();

        foreach ($collection as $order) {
            $customerData = [];
            $customerData[] = $order->getNsSoNumber();
            $customerData[] = $order->getCustomerEmail();
            $customerData[] = $order->getAgencyName();
            $customerData[] = $order->getAgencyName();
            $customerData[] = $order->getSku();
            $customerData[] = date('M d, Y', strtotime($order->getCustomerTs()));
            $customerData[] = $order->getStatus();
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
