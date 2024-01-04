<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnBuilder\Eav\Relation;

interface BuilderInterface
{
    /**
     * Build eav sub-select join relation
     *
     * @param array $columnData
     * @param string $linkedField
     * @param string $tableName
     * @return array
     */
    public function execute(array $columnData, string $linkedField, string $indexField, string $tableName): array;

    /**
     * @param string $tableName
     *
     * @return bool
     */
    public function isApplicable(string $tableName): bool;
}
