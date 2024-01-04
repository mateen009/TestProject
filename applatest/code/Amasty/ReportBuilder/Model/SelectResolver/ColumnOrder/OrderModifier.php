<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnOrder;

use Amasty\ReportBuilder\Model\ResourceModel\Report\Data\Select;

class OrderModifier implements OrderModifierInterface
{
    /**
     * @var OrderStorageInterface
     */
    private $storage;

    public function __construct(
        OrderStorageInterface $storage
    ) {
        $this->storage = $storage;
    }

    public function modify(string $columnName, string $direction = Select::SQL_DESC): void
    {
        $direction = strtoupper($direction) == Select::SQL_ASC ? Select::SQL_ASC : Select::SQL_DESC;

        $this->storage->addOrder($columnName, $direction);
    }
}
