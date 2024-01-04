<?php
    
namespace AscentDigital\NetsuiteConnector\Model\ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class SaveItemDetails extends AbstractDb{
    
    protected function _construct(){
        $this->_init('items_details', 'id');
    }
}
