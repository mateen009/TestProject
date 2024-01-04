<?php
    
namespace AscentDigital\NetsuiteConnector\Model\ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class NSCron extends AbstractDb{
    
    protected function _construct(){
        $this->_init('ns_cron_iteration', 'id');
    }
}
