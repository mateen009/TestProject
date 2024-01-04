<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnOrder;

class OrderStorage implements OrderStorageInterface
{
    /**
     * @var array
     */
    private $orders = [];

    public function addOrder(string $columnName, string $direction): void
    {
        $this->orders[$columnName] = $direction;
    }

    public function removeOrder(string $columnName): void
    {
        unset($this->orders[$columnName]);
    }

    public function getAllOrders(): array
    {
        return $this->orders;
    }
}
