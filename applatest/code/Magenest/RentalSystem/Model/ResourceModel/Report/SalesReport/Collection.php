<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Report Sold Products collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magenest\RentalSystem\Model\ResourceModel\Report\SalesReport;

use Magento\Framework\DB\Select;

/**
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Reports\Model\ResourceModel\Order\Collection
{
    /**
     * Set Date range to collection
     *
     * @param int $from
     * @param int $to
     * @return $this
     */
    public function setDateRange($from, $to)
    {
        $this->_reset()
            ->addAttributeToSelect('*')
            ->addOrderedQty($from, $to)
            ->setOrder('created_order');
        return $this;
    }

    /**
     * Add ordered qty's
     *
     * @param string $from
     * @param string $to
     * @return $this
     */
    public function addOrderedQty($from = '', $to = '')
    {
        $connection = $this->getConnection();
        $orderTableAliasName = $connection->quoteIdentifier('order');

        $orderJoinCondition = [
            $orderTableAliasName . '.entity_id = order_items.order_id',
        ];

        $rentalOrderTableAliasName = $connection->quoteIdentifier('rental_order');
        $orderJoinConditionRental = [
            $rentalOrderTableAliasName . '.order_item_id = order_items.item_id'
        ];

        $this->getSelect()->reset()->from(
            ['order_items' => $this->getTable('sales_order_item')],
            [
                'created_order' => 'COUNT(DISTINCT(order.entity_id))',
                'order_items_name' => 'order_items.name',
                'revenue' => 'SUM(order_items.price * (order_items.qty_invoiced - order_items.qty_refunded))'
            ]
        )->joinLeft(
            ['order' => $this->getTable('sales_order')],
            implode(' AND ', $orderJoinCondition),
            []
        )->joinLeft(
            ['rental_order' => $this->getTable('magenest_rental_order')],
            implode(' AND ', $orderJoinConditionRental),
            []
        )->where(
            'order_items.parent_item_id IS NULL'
        )->where(
            "(order_items.created_at) BETWEEN '$from' AND '$to'"
        )->where(
            "order_items.product_type = 'rental'"
        )->group(['order_items_name']);
        return $this;
    }

    /**
     * Set store filter to collection
     *
     * @param array $storeIds
     * @return $this
     */
    public function setStoreIds($storeIds)
    {
        if ($storeIds) {
            $this->getSelect()->where('order_items.store_id IN (?)', (array)$storeIds);
        }
        return $this;
    }

    /**
     * Set order
     *
     * @param string $attribute
     * @param string $dir
     * @return $this
     */
    public function setOrder($attribute, $dir = self::SORT_ORDER_DESC)
    {
        if ($attribute == 'orders') {
            $this->getSelect()->order($attribute . ' ' . $dir);
        } else {
            parent::setOrder($attribute, $dir);
        }

        return $this;
    }

    /**
     * @return Select
     * @since 100.2.0
     */
    public function getSelectCountSql()
    {
        $countSelect = clone parent::getSelectCountSql();

        $countSelect->reset(Select::COLUMNS);
        $countSelect->columns('COUNT(DISTINCT order_items.item_id)');

        return $countSelect;
    }
}
