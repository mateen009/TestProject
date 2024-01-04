<?php

namespace AscentDigital\NetsuiteConnector\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CustomerFileUpload extends AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('customer_files', 'id'); // customer_files is the database table
    }
}