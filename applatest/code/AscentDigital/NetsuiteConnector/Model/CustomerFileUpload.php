<?php

namespace AscentDigital\NetsuiteConnector\Model;

use Magento\Framework\Model\AbstractModel;

class CustomerFileUpload extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('AscentDigital\NetsuiteConnector\Model\ResourceModel\CustomerFileUpload');
    }
}