<?php

namespace AscentDigital\Reports\Helper\AdminReports;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;

class OrderInventoryReportCsv extends AbstractHelper
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
        $filepath = 'export/order_inventory_report_csv.csv';
        $this->directory->create('export');
        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();

        $header = ['Item', 'Total Order', 'Total Quantity', 'On Demo', 'Due', 'Returned'];
        $stream->writeCsv($header);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $inventoryBlock = $objectManager->get('AscentDigital\Reports\Block\Adminhtml\Reports\OrderInventoryReport');
        $collection= $inventoryBlock->getProducts();
        foreach ($collection as $item) {
            $customerData = [];
            $customerData[] = $item->getName();
            $customerData[] = $item->getTotalOrders();
            $customerData[] = $item->getTotalQty();
            $customerData[] = $inventoryBlock->getOnDemo($item->getSku());
            $customerData[] = $inventoryBlock->getDue($item->getSku());
            $customerData[] = $inventoryBlock->getReturned($item->getSku());
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

    /* Header Columns */
    public function getColumnHeader()
    {
        $headers = ['Item', 'Total Order', 'Total Quantity', 'On Demo', 'Due', 'Returned'];
        return $headers;
    }
}
