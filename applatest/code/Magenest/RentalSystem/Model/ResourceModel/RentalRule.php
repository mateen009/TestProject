<?php
namespace Magenest\RentalSystem\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class RentalRule extends AbstractDb
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'magenest_rental_rule_resource_model';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('magenest_rental_rule', 'entity_id');
    }
}
