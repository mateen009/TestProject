<?php
namespace AscentDigital\NetsuiteConnector\Block\Adminhtml\Edit\Tab\FileTable;

use AscentDigital\NetsuiteConnector\Model\ResourceModel\CustomerFileUpload\Collection;
class FileTable extends \Magento\Backend\Block\Template
{
   /**
    * Block template.
    *
    * @var string
    */
   protected $_template = 'tab/file_table.phtml';

   public function __construct(
      \Magento\Backend\Block\Template\Context $context,   
      \Magento\Store\Model\StoreManagerInterface $storeManager, 
      \Magento\Framework\Message\ManagerInterface $messageManager,
      Collection $customerFileUploadFactory,    
      array $data = []
  )
  {    
      parent::__construct($context, $data);
      $this->_storeManager = $storeManager;
      $this->messageManager = $messageManager;
      $this->customerFileUploadFactory = $customerFileUploadFactory;
  }

   public function getCustomerFiles(){
      $collection = $this->customerFileUploadFactory;
      $collection->addFieldToFilter('customer_id',$this->getCustomerId());
      // print_r($collection->getData());die('mateeen');
      return $collection;
   }
   
}