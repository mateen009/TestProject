<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\View;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Column\FilterConditionType;
use Amasty\ReportBuilder\Model\ResourceModel\Report\Data\Collection;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnBuilder\Eav\Relation\StoreIdRegistry;

class FilterApplier
{
    /**
     * @var FiltersProvider
     */
    private $filtersProvider;

    /**
     * @var StoreIdRegistry
     */
    private $storeIdRegistry;

    public function __construct(
        FiltersProvider $filtersProvider,
        StoreIdRegistry $storeIdRegistry
    ) {
        $this->filtersProvider = $filtersProvider;
        $this->storeIdRegistry = $storeIdRegistry;
    }

    public function execute(ReportInterface $report, Collection $collection): void
    {
        $this->addDateFilter($report, $collection);
        $this->addStoreFilter($report, $collection);
        $collection->setInterval($this->filtersProvider->getInterval());
    }

    private function addDateFilter(ReportInterface $report, Collection $collection): void
    {
        foreach ($report->getAllColumns() as $columnId => $columnData) {
            if (isset($columnData[ColumnInterface::IS_DATE_FILTER]) && $columnData[ColumnInterface::IS_DATE_FILTER]) {
                $collection->addFieldToFilter(
                    str_replace('.', '_', $columnId),
                    $this->filtersProvider->getDateFilter()
                );
                break;
            }
        }
    }

    private function addStoreFilter(ReportInterface $report, Collection $collection): void
    {
        $storeId = $this->filtersProvider->getStoreId();
        if ($storeId) {
            $this->storeIdRegistry->setStoreId($storeId);
            $collection->addFieldToFilter(
                sprintf('%s.store_id', $report->getMainEntity()),
                [FilterConditionType::CONDITION_VALUE => $storeId]
            );
        }
    }
}
