<?php

namespace AscentDigital\NetsuiteConnector\Model;

use Magento\Framework\Model\AbstractModel;

class SaveItemDetails extends AbstractModel{
    protected function _construct(){
        $this->_init('AscentDigital\NetsuiteConnector\Model\ResourceModel\SaveItemDetails');
    }
}
