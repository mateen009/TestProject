<?php

namespace AscentDigital\Reports\Controller\Adminhtml\Reports;
use Magento\Framework\App\Filesystem\DirectoryList;

class OrderInventoryReportCsv extends \Magento\Backend\App\Action
{

    protected $resultPageFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->customerFactory = $customerFactory;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        parent::__construct($context);
    }

    public function execute()
    {
        $filepath = 'export/customerlist.csv';
        $this->directory->create('export');
        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();
        $header = ['Id', 'Name', 'Email'];
        $stream->writeCsv($header);
        $collection = $this->customerFactory->create()->getCollection();
        foreach ($collection as $customer) {
            $data = [];
            $data[] = $customer->getId();
            $data[] = $customer->getName();
            $data[] = $customer->getEmail();
            $stream->writeCsv($data);
        }
        $redirect = $this->resultPageFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        // $redirect->setUrl('/redirect/to/destination');

        // return $redirect;
    }

    protected function _isAllowed()
    {
        return true;
    }
}
