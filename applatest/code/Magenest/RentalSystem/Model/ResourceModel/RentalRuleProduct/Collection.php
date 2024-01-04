<?php

namespace Magenest\RentalSystem\Model\ResourceModel\RentalRuleProduct;

use Magenest\RentalSystem\Model\RentalRuleProduct as Model;
use Magenest\RentalSystem\Model\ResourceModel\RentalRuleProduct as ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'magenest_rental_rule_product_collection';

    /** @var bool */
    private $_joined = false;

    /** @var bool */
    private $_enableJoin = false;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }

    /**
     * @return void
     */
    private function joinRuleData()
    {
        $this->getSelect()->joinInner(
            ['rule_data' => $this->getTable('magenest_rental_rule')],
            'main_table.rule_id = rule_data.entity_id'
        );
        $this->_joined = true;
    }

    /**
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        if ($this->_joined === false && $this->_enableJoin === true) {
            $this->joinRuleData();
        }
        parent::_renderFiltersBefore();
    }

    /**
     * @param $bool
     * @return Collection
     */
    public function setJoin($bool)
    {
        $this->_enableJoin = $bool;
        return $this;
    }
}
