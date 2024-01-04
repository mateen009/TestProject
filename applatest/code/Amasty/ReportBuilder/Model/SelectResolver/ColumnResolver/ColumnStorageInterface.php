<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver;

interface ColumnStorageInterface
{
    public function addColumn(string $columnId, array $columnConfig): void;

    public function getColumnById(string $columnId): array;

    public function getAllColumns(): array;

    public function setColumns(array $columns): void;
}
