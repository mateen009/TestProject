<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnModifiers\ExpressionModifiers;

interface ModifierInterface
{
    /**
     * @param string $columnId
     * @param array $selectColumnData
     * @return array
     */
    public function modify(string $columnId, array $selectColumnData): array;
}
