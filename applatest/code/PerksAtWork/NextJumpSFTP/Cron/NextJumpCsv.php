<?php

namespace PerksAtWork\NextJumpSFTP\Cron;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Filesystem;


class NextJumpCsv
{
   
protected $filesystem;
protected $fileFactory;
    protected $orderRepository;
    protected $searchCriteriaBuilder;
    protected $collectionFactory;
    public function __construct(FileFactory $fileFactory,
    SearchCriteriaBuilder $searchCriteriaBuilder,
    \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection,
    \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
    Filesystem $filesystem,
    OrderRepositoryInterface $orderRepository)
    {
        $this->fileFactory = $fileFactory;
    $this->orderRepository = $orderRepository;
    $this->collectionFactory = $collectionFactory;
    $this->orderCollection = $orderCollection;
    $this->_filesystem = $filesystem;
    $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function execute(){
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/nextjump_csv.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('NextJumpCsv cron is running');
  
    $to = date("Y-m-d h:i:s"); // current date
    $from = strtotime('-3 day', strtotime($to));
    $from = date('Y-m-d h:i:s', $from); // 2 days before
  
    
      $ordersList =  $this->orderCollection->create();
      $ordersList->addFieldToFilter('created_at', array('from'=>$from, 'to'=>$to))
        ->addFieldToFilter('is_csv_generated','no')
      ->addFieldToFilter('store_id',array('eq'=>7));
      
      foreach( $ordersList  as $order){
          
    $orderItems = $order->getAllVisibleItems();
    $itemQty = array();
    $itemSku = array();
    $itemName = array();
    $itemRowTotal = array();
    $status= 'Sale';
    $commission= 0;
    foreach ($orderItems as $item) {
        $itemQty[]=    rtrim($item->getQtyOrdered(),'.0');
        
        
        $itemSku[]=$item->getSku();
        
        $itemName[]=$item->getName();
        $itemRowTotal[]=number_format($item->getRowTotal(),2);
        
        
        
    }
    $csvData[] = array(
        $item->getOrderId(),
        $order->getCreatedAt(),
        number_format($order->getSubtotal(),2),
        $order->getBaseCurrencyCode(),
        implode(' ,',$itemQty),
        implode(' ,',$itemSku),
        implode(' ,',$itemName),
        implode(' ,',$itemRowTotal),
        
        $order->getIncrementId(),
        
        $order->getCustomerEmail(),
        $status,
        $commission,
        
        $order->getCsid(),
    );
    $order->setData('is_csv_generated','yes');
    $order->save();
}


    
    $fileName = 'orderscsv.csv';
    $filePath = 'export/' . $fileName;
    $directory = $this->_filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
    
    $stream = $directory->openFile($filePath, 'w+');
    $header = ['MERCHANTID', 'PURCHASEDATE', 'ORDERSUBTOTAL','CURRENCY','QUANTITY','SKU','SKUDESC','ITEMSUBTOTAL','ORDERNUMBER','EMAIL','STATUS','COMMISSION','CSID'];
    $stream->writeCsv($header);
    if(isset($csvData)){
    foreach ($csvData as $rowData) {
    $stream->writeCsv($rowData);
    }
    $stream->close();
    
    $content = [
    'type' => 'filename',
    'value' => $filePath,
    'rm' => false
    ];
    
    
    $this->fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    $this->csvgenerate();
}
    $logger->info('NextJumpCsv cron is running');
    }
    
    public function csvgenerate()
    {
      try{  
        $to = date("Ymd"); 
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');

        $sftp = $objectManager->create('Magento\Framework\Filesystem\Io\Sftp');
        $sftp->open(
            array(
                'host' => 'eft.nextjump.com',
                'username' => 'mobilitycg',
                'password' => 'x3s50y+Tr!-HE38WR$Xi',
                'port' =>22,
                'passive' => true
            )
        );
        
        $fileName = 'orderscsv.csv';
        $content = file_get_contents($directory->getPath('var') . '/export/' . $fileName);
        $sftp->write('/test/NextJump_Daily_'.$to.'.csv', $content);
        $sftp->close();
        echo "File uploaded..!";
        }
        catch(\Exception $ex){
        echo 'File not Uploaded';
        }
       
    }
    
    
    
    }
