<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnFilter;

interface FilterConditionResolverInterface
{
    /**
     * Resolve column filter condition
     *
     * @param string $columnType
     * @param array $condition
     * @return array $condition
     */
    public function resolve(string $columnType, array $condition): array;
}
