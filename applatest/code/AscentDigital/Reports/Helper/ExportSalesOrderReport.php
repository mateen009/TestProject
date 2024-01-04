<?php

namespace AscentDigital\Reports\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
    
class ExportSalesOrderReport extends AbstractHelper{
    /** @var ResultFactory */
    protected $resultFactory;

    protected $_storeManager;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    protected $_orderCollectionFactory;
    protected $customerFactory;

    /**
     * @param Context $context
     * @param ResultFactory $resultFactory
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Customer\Model\Session $customerSession,
        CustomerFactory $customerFactory
    ) {
        $this->_fileFactory = $fileFactory;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->resultFactory = $resultFactory;
        $this->_customerSession = $customerSession;
        $this->customerFactory = $customerFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function exportData($orders)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $blockViewOrder =  $objectManager->create('AscentDigital\Reports\Block\Reports\FirstNet\SalesBySaleRepAdmin');
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
            $orderData[] = $order->getCustomerEmail();
            $orderData[] = $blockViewOrder->getOrderCount($order->getCustomerEmail());
            $orderData[] = $blockViewOrder->getDemoOrderCount($order->getCustomerEmail());
            $orderData[] = $blockViewOrder->getLateOrderCount($order->getCustomerEmail());
            $orderData[] = $blockViewOrder->getCompletedOrderCount($order->getCustomerEmail());
            $orderData[] = $order->getTotalQtyOrdered();
            $orderData[] = $order->getSmEmail();
            $orderData[] =  $order->getTmEmail();
            $orderData[] =  $order->getEmEmail();
            $stream->writeCsv($orderData);
            
        }

        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '1'; //remove csv from var folder

        $csvfilename = 'Sales Rep Admin.csv';
        return $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);

         $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
         $redirect->setUrl('/redirect/to/destination');
         return $redirect;
    }

    /* Header Columns */
    public function getColumnHeader()
    {
        $headers = ['Sales Rep Email', 'Total Orders','On Demo ','Due','Completed', 'Total Qty','SM Email','TM Email','EM Email'];
        return $headers;
    }
}


