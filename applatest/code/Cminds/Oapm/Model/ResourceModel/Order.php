<?php
namespace Cminds\Oapm\Model\ResourceModel;

class Order extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Set main entity table name and primary key field name.
     */
    protected function _construct()
    {
        $this->_init(
            \Cminds\Oapm\Setup\InstallSchema::TABLE_NAME,
            \Cminds\Oapm\Setup\InstallSchema::PRIMARY_KEY
        );
    }
}
