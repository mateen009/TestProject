<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnOrder;

use Amasty\ReportBuilder\Model\ResourceModel\Report\Data\Select;

interface OrderModifierInterface
{
    /**
     * Modify order in storage
     *
     * @param string $columnName
     * @param string $direction
     */
    public function modify(string $columnName, string $direction = Select::SQL_ASC): void;
}
