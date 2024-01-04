<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnFilter;

class FilterStorage implements FilterStorageInterface
{
    /**
     * @var array
     */
    private $filters = [];

    public function addFilter(string $columnName, array $condition): void
    {
        if (isset($this->filters[$columnName])) {
            $this->filters[$columnName] += $condition;
        } else {
            $this->filters[$columnName] = $condition;
        }
    }

    public function removeFilter(string $columnName): void
    {
        unset($this->filters[$columnName]);
    }

    public function removeAllFilters(): void
    {
        $this->filters = [];
    }

    public function getAllFilters(): array
    {
        return $this->filters;
    }
}
