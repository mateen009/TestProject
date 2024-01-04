<?php
// CHM-MA

namespace AscentDigital\Reports\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
    
class ExportOrderInventory extends AbstractHelper{
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
    public function exportData()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $block =  $objectManager->create('AscentDigital\Reports\Block\Reports\FirstNet\OrderInventoryReport');
        $orders =$block->getProducts();
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
       // foreach ($orders as $order) {
            
        foreach($orders as $item){
            // echo "<pre>";print_r($item->getData());die("ero");
            $orderData = [];
            $orderData[] = $item->getName();
            $orderData[] = $item->getSku();
            $orderData[] = $item->getTotalOrders();
            $orderData[] = $item->getTotalQty();
            $orderData[] = $block->getOnDemo($item->getSku());
            $orderData[] = $block->getDue($item->getSku());
            $orderData[] = $block->getReturned($item->getSku());
            $stream->writeCsv($orderData);
            
            }
      //  }

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
        $headers = ['Items','Sku', 'Total Order', 'Total Quantity','On Demo','Due','Returned'];
        return $headers;
    }
}
