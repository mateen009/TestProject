<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnOrder;

interface OrderStorageInterface
{
    /**
     * Add order to storage
     *
     * @param string $columnName
     * @param string $direction
     */
    public function addOrder(string $columnName, string $direction): void;

    /**
     * Remove order from storage
     *
     * @param string $columnName
     */
    public function removeOrder(string $columnName): void;

    /**
     * Get all existed orders
     *
     * @return array
     */
    public function getAllOrders(): array;
}
