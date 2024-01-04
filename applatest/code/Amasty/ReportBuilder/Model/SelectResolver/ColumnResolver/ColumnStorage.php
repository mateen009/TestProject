<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver;

class ColumnStorage implements ColumnStorageInterface
{
    /**
     * @var array
     */
    private $columns = [];

    public function addColumn(string $columnId, array $columnConfig): void
    {
        $this->columns[$columnId] = $columnConfig;
    }

    public function getColumnById(string $columnId): array
    {
        return $this->columns[$columnId] ?? [];
    }

    public function getAllColumns(): array
    {
        return $this->columns;
    }

    public function setColumns(array $columns): void
    {
        foreach ($columns as $columnId => $columnConfig) {
            $this->addColumn($columnId, $columnConfig);
        }
    }
}
