<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnFilter;

interface FilterModifierInterface
{
    /**
     * Modify filter in storage
     *
     * @param string $columnName
     * @param array $condition
     */
    public function modify(string $columnName, ?array $condition = null): void;
}
