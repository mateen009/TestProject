<?php

namespace AscentDigital\Reports\Helper\AdminReports;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;

class OrderInventory extends AbstractHelper
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
    
    public function exportData($orders)
       {
           
           $name = date('m_d_Y_H_i_s');
           $filepath = 'export/custom' . $name . '.csv';
           $this->directory->create('export');
           /* Open file */
           $stream = $this->directory->openFile($filepath, 'w+');
           $stream->lock();
           $columns = $this->getColumnHeader();
           foreach ($columns as $column) {
               $header[] = $column;
           }
               
           
          
           $stream->writeCsv($header);
           foreach ($orders as $order) {
               $orderData = [];
               $orderData[] = $order->getEntityId();
               $orderData[] = $order->getName();
               $orderData[] = $order->getSku();
               $orderData[] = $order->getQty();
               $orderData[] = $order->getQty()+$order->getReserveQty();
               
               $stream->writeCsv($orderData);
               
           }

           $content = [];
           $content['type'] = 'filename'; // must keep filename
           $content['value'] = $filepath;
           $content['rm'] = '1'; //remove csv from var folder

           $csvfilename = 'Order Inventory.csv';
           return $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);

            $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $redirect->setUrl('/redirect/to/destination');
            return $redirect;
       }

       /* Header Columns */
       public function getColumnHeader()
       {
           $headers = ['Product Id', 'Name', 'Sku', 'Stock', 'Saleable Stock'];
           return $headers;
       }
}
