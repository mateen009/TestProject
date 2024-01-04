<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnFilter;

interface FilterStorageInterface
{
    /**
     * Add filter to storage
     *
     * @param string $columnName
     * @param array $condition
     */
    public function addFilter(string $columnName, array $condition): void;

    /**
     * Remove filter from storage
     *
     * @param string $columnName
     */
    public function removeFilter(string $columnName): void;

    /**
     * Remove all existed filters
     *
     * @return void
     */
    public function removeAllFilters(): void;

    /**
     * Get all existed filters
     *
     * @return array
     */
    public function getAllFilters(): array;
}
