<?php
 
namespace Orders\Data\Controller;
 
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
 
class FilterOrder extends Action
{
    protected $_resultPageFactory;

    protected $_resultJsonFactory;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        PageFactory $resultJsonFactory

    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);

    }
        
    public function execute() {
        $objectManager =   \Magento\Framework\App\ObjectManager::getInstance();
    $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION'); 
    $result1 = $connection->fetchAll("SELECT * FROM items_details");

    echo "<pre>";print_r($result1);
    }
    
    
}
