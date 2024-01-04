<?php

namespace AscentDigital\Reports\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
    
class ExportMobileCgReports extends AbstractHelper{
    /** @var ResultFactory */
    protected $resultFactory;
    /**
     * @param Context $context
     * @param ResultFactory $resultFactory
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Controller\ResultFactory $resultFactory
    ) {
        $this->_fileFactory = $fileFactory;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->resultFactory = $resultFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function exportInventoryRportCsv($productsData)
    {
      //echo "<pre>";print_r($productsData);die();
    //   $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    //   $blockViewOrder =  $objectManager->create('AscentDigital\Reports\Block\Reports\MobileCg\InventoryReport');

    ob_end_clean();

      $name = date('m_d_Y_H_i_s');
      $filepath = 'export/custom' . $name . '.csv';
      $this->directory->create('export');
      /* Open file */
      $stream = $this->directory->openFile($filepath, 'w+');
      $stream->lock();
      //$columns = $this->getColumnHeader();
      $columns = ['Sku', 'Name', 'Quantity'];
      foreach ($columns as $column) {
          $header[] = $column;
      }
      
      $stream->writeCsv($header);
      foreach ($productsData as $data) {
          $_productsData = [];
          $_productsData[] = $data['sku'];
          $_productsData[] = $data['name'];
          $_productsData[] = $data['qty'];
          $stream->writeCsv($_productsData);              
      }
      
      // echo "<pre>";print_r($_productsData);die();

      $content = [];
      $content['type'] = 'filename'; // must keep filename
      $content['value'] = $filepath;
      $content['rm'] = '1'; //remove csv from var folder

      $csvfilename = 'Inventory Report.csv';
      $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
      exit();
      return;
    }

    public function exportOrderTypeRportCsv($ordersData)
    {
        //echo "<pre>";print_r($ordersData);die();

        ob_end_clean();

        $name = date('m_d_Y_H_i_s');
        $filepath = 'export/custom' . $name . '.csv';
        $this->directory->create('export');
        /* Open file */
        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();
        //$columns = $this->getColumnHeader();
        $columns = ['Order Type', 'Total Orders', 'Total Ordered Quantity'];
        foreach ($columns as $column) {
            $header[] = $column;
        }
        
        $stream->writeCsv($header);
        
        foreach ($ordersData as $type => $qtyData) {
            //echo "<pre>";print_r($data);die('::');
            $_oTypeData = [];
            $_oTypeData[] = $type;
            $_oTypeData[] = $qtyData['totalOrders'];
            $_oTypeData[] = $qtyData['totalQty'];
            $stream->writeCsv($_oTypeData);
        }           
        
         //echo "<pre>";print_r($_oTypeData);die();

        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '1'; //remove csv from var folder

        $csvfilename = 'Order Type Report.csv';
        $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
        
        exit();
        return;
    }
    
    function exportNewCsv() {
        // header("Content-type: text/csv");
        // header("Content-Disposition: attachment; filename=result_file.csv");
        // header("Pragma: no-cache");
        // header("Expires: 0");

        $data = array(
        array('aaa', 'bbb', 'ccc', 'dddd'),
        array('123', '456', '789'),
        array('aaa', 'bbb')
        );

        $this->outputCSV($data);
    }

    function outputCSV($data) {
        ob_end_clean();

        $output = fopen("php://output", "w");
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=data.csv');

        exit();
    }

    public function exportAERportCsv($ordersData)
    {
        //echo "<pre>";print_r($ordersData);die();
        ob_end_clean();

        $name = date('m_d_Y_H_i_s');
        $filepath = 'export/custom' . $name . '.csv';
        $this->directory->create('export');
        /* Open file */
        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();
        //$columns = $this->getColumnHeader();
        $columns = ['Order Type', 'Order Quantity'];
        foreach ($columns as $column) {
            $header[] = $column;
        }
        
        $stream->writeCsv($header);
        
        foreach ($ordersData as $type => $quantity) {
            $_aeData = [];
            $_aeData[] = $type;
            $_aeData[] = $quantity;
            $stream->writeCsv($_aeData);   
        }
        // echo "<pre>";print_r($_aeData);die();

        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '1'; //remove csv from var folder

        $csvfilename = 'AE Report - YTD.csv';
        $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
        exit();
        return;
    }

    public function exportAERportByTypeCsv($selectedOrders)
    {
        //echo "<pre>";print_r($ordersData);die();
        ob_end_clean();
        
        $name = date('m_d_Y_H_i_s');
        $filepath = 'export/custom' . $name . '.csv';
        $this->directory->create('export');
        /* Open file */
        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();
        //$columns = $this->getColumnHeader();
        $columns = ['Order #', 'Order Type', 'Order Status', 'Customer Email', 'Total Amount'];
        foreach ($columns as $column) {
            $header[] = $column;
        }
        
        $stream->writeCsv($header);
        
        foreach ($selectedOrders as $order) {
            $_otData = [];
            $_otData[] = $order->getIncrementId();
            $_otData[] = $order->getOrderType();
            $_otData[] = $order->getStatus();
            $_otData[] = $order->getCustomerEmail();
            $_otData[] = number_format((float)$order->getBaseGrandTotal(), 2, '.', '');
            $stream->writeCsv($_otData);   
        }
         //echo "<pre>";print_r($_oTypeData);die();

        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '1'; //remove csv from var folder

        $csvfilename = 'AE Report(by type) - YTD - .csv';
        $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
        exit();
        return;
    }

    public function exportRepairRportCsv($repairData)
    {
        //echo "<pre>";print_r($data);die();
        ob_end_clean();

        $name = date('m_d_Y_H_i_s');
        $filepath = 'export/custom' . $name . '.csv';
        $this->directory->create('export');
        /* Open file */
        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();
        //$columns = $this->getColumnHeader();
        $columns = ['Item Name', 'Request Qty', 'Condition', 'Request Date', 'Customer Name', 'Reason', 'Status'];
        foreach ($columns as $column) {
            $header[] = $column;
        }
        
        $stream->writeCsv($header);
        foreach ($repairData as $data) {
            $_repairData = [];
            $_repairData[] = $data['productName'];
            $_repairData[] = $data['request_qty'];
            $_repairData[] = $data['conditionTitle'];
            $_repairData[] = $data['created_at'];
            $_repairData[] = $data['customer_name'];
            $_repairData[] = $data['reason'];
            $_repairData[] = $data['statusTitle'];
            $stream->writeCsv($_repairData);              
        }
        
         //echo "<pre>";print_r($_repairData);die();

        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '1'; //remove csv from var folder

        $csvfilename = 'Repair Report.csv';
        $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
        exit();
        return;
    }

    public function exportLocationInventoryRportCsv($locationData)
    {
        //echo "<pre>";print_r($locationData);die('::');
        ob_end_clean();

        $name = date('m_d_Y_H_i_s');
        $filepath = 'export/custom' . $name . '.csv';
        $this->directory->create('export');
        /* Open file */
        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();
        //$columns = $this->getColumnHeader();
        $columns = ['Id', 'Item Id', 'Item Name', 'Sku', 'Item Internal Id', 'Location Id', 'Qty'];
        foreach ($columns as $column) {
            $header[] = $column;
        }
        
        $stream->writeCsv($header);
        foreach ($locationData as $data) {
            //echo "<pre>";print_r($data);die('::');
            $_locationData = [];
            $_locationData[] = $data['id'];
            $_locationData[] = $data['itemId'];
            $_locationData[] = $data['itemName'];
            $_locationData[] = $data['itemSku'];
            $_locationData[] = $data['internalId'];
            $_locationData[] = $data['locationId'];
            $_locationData[] = $data['qty'];
            $stream->writeCsv($_locationData);              
        }
        
         //echo "<pre>";print_r($_repairData);die();

        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '1'; //remove csv from var folder

        $csvfilename = 'Location Inventory Report.csv';
        $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
        exit();
        return;

    }

    /* Header Columns */
    public function getColumnHeader()
    {
        $headers = ['Sku', 'Name', 'Quantity'];
        return $headers;
    }
    
}
