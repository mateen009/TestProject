<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnBuilder;

use Amasty\ReportBuilder\Model\ResourceModel\Report\Data\Select;

interface BuilderInterface
{
    /**
     * @param Select $select
     * @param string $columnId
     * @param array $columnData
     * @return void
     */
    public function build(Select $select, string $columnId, array $columnData): void;
}
