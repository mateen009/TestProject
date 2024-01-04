<?php

namespace AscentDigital\Reports\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
    
class ExportSalesRepOrders extends AbstractHelper{
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
            foreach($order->getAllItems() as $item){
            $orderData = [];
            $orderData[] = $order->getNsSoNumber();
            $orderData[] = $order->getCustomerEmail();
            $orderData[] = $order->getAgencyName();
            $orderData[] = date('M d, Y', strtotime($order->getCustomerTs()));
            $orderData[] = $order->getStatusLabel();
            $orderData[] = $item->getName();
            $orderData[] =  $item->getSku();
            if($order->getReturnStatus()=='No' && $order->getStatusLabel()=='On Demo'){
              $orderData[] = 'Yes';
            }else{
             $orderData[] = 'No';
            }
            
            $stream->writeCsv($orderData);
            }
        }

        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '1'; //remove csv from var folder

        $csvfilename = 'Sales Rep Orders.csv';
        return $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);

         $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
         $redirect->setUrl('/redirect/to/destination');
         return $redirect;
    }

    /* Header Columns */
    public function getColumnHeader()
    {
        $headers = ['SO #', 'Email', 'Agency Name', 'Date', 'Status','Due Order','Items','Sku'];
        return $headers;
    }
    
}
