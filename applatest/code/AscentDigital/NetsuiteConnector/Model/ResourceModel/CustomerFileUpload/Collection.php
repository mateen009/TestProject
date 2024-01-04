<?php

namespace AscentDigital\NetsuiteConnector\Model\ResourceModel\CustomerFileUpload;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'AscentDigital\NetsuiteConnector\Model\CustomerFileUpload',
            'AscentDigital\NetsuiteConnector\Model\ResourceModel\CustomerFileUpload'
        );
    }
}