<?php
namespace Magenest\RentalSystem\Model\ResourceModel\RentalRule;

use Magenest\RentalSystem\Model\RentalRule as Model;
use Magenest\RentalSystem\Model\ResourceModel\RentalRule as ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'magenest_rental_rule_collection';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
