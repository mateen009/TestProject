<?php

namespace AscentDigital\NetsuiteConnector\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
class Collection extends AbstractCollection{
    
    protected function _construct(){
        $this->_init(
            'AscentDigital\NetsuiteConnector\Model\SaveItemDetails',
            'AscentDigital\NetsuiteConnector\Model\ResourceModel\SaveItemDetails'
        );
    }
}
