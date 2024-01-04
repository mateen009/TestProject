<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Model\ResourceModel\Report as ReportResource;
use Magento\Framework\DB\Select;

class Report extends \Magento\Framework\Model\AbstractModel implements ReportInterface
{
    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ReportResource::class);
    }

    public function getReportId(): int
    {
        return (int) $this->getData(ReportInterface::REPORT_ID);
    }

    public function setReportId(?int $reportId): void
    {
        $this->setData(ReportInterface::REPORT_ID, $reportId);
    }

    public function getName(): ?string
    {
        return $this->getData(ReportInterface::NAME);
    }

    public function setName(string $name): void
    {
        $this->setData(ReportInterface::NAME, $name);
    }

    public function getMainEntity(): ?string
    {
        return $this->getData(ReportInterface::MAIN_ENTITY);
    }

    public function setMainEntity(string $entity): void
    {
        $this->setData(ReportInterface::MAIN_ENTITY, $entity);
    }

    public function getStoreIds(): array
    {
        $storeIds = $this->getData(ReportInterface::STORE_IDS) ?? [];

        if (!is_array($storeIds)) {
            $storeIds = explode(',', $storeIds);
        }

        return $storeIds;
    }

    public function setStoreIds(array $storeIds = []): void
    {
        $this->setData(ReportInterface::STORE_IDS, $storeIds);
    }

    public function getUsePeriod(): bool
    {
        return (bool)$this->getData(ReportInterface::USE_PERIOD);
    }

    public function setUsePeriod(bool $usePeriod = false): void
    {
        $this->setData(ReportInterface::USE_PERIOD, $usePeriod);
    }

    public function getDisplayChart(): bool
    {
        return (bool)$this->getData(ReportInterface::DISPLAY_CHART);
    }

    public function setDisplayChart(bool $displayChart = false): void
    {
        $this->setData(ReportInterface::DISPLAY_CHART, $displayChart);
    }

    public function getChartAxisX(): ?string
    {
        return $this->getData(ReportInterface::CHART_AXIS_X);
    }

    public function setChartAxisX(string $columnId): void
    {
        $this->setData(ReportInterface::CHART_AXIS_X, $columnId);
    }

    public function getChartAxisY(): ?string
    {
        return $this->getData(ReportInterface::CHART_AXIS_Y);
    }

    public function setChartAxisY(string $columnId): void
    {
        $this->setData(ReportInterface::CHART_AXIS_Y, $columnId);
    }

    public function setColumns(array $columns): void
    {
        $this->setData(ReportInterface::COLUMNS, $columns);
    }

    public function getAllColumns(): array
    {
        return $this->getData(ReportInterface::COLUMNS) ?? [];
    }

    public function setRelationScheme(array $scheme): void
    {
        $this->setData(ReportInterface::SCHEME, $scheme);
    }

    public function getRelationScheme(): array
    {
        return $this->getData(ReportInterface::SCHEME) ?? [];
    }

    public function getAllFilters(): array
    {
        $filters = [];

        foreach ($this->getAllColumns() as $columnData) {
            if (isset($columnData[ReportInterface::REPORT_COLUMN_ID])
                && isset($columnData[ReportInterface::REPORT_COLUMN_FILTER])
                && !empty($columnData[ReportInterface::REPORT_COLUMN_ID])
                && !empty($columnData[ReportInterface::REPORT_COLUMN_FILTER])
            ) {
                $filters[$columnData[ReportInterface::REPORT_COLUMN_ID]]
                    = $columnData[ReportInterface::REPORT_COLUMN_FILTER];
            }
        }

        return $filters;
    }

    public function getAllEntities(): array
    {
        $relations = $this->getRelationScheme();
        $entities = [];

        foreach ($relations as $relation) {
            if (!in_array($relation[ReportInterface::SCHEME_SOURCE_ENTITY], $entities)) {
                $entities[] = $relation[ReportInterface::SCHEME_SOURCE_ENTITY];
            }
            if (!in_array($relation[ReportInterface::SCHEME_ENTITY], $entities)) {
                $entities[] = $relation[ReportInterface::SCHEME_ENTITY];
            }
        }

        return $entities;
    }

    private function getSortingColumn(): ?array
    {
        foreach ($this->getAllColumns() as $column) {
            if ($column[ColumnInterface::ORDER] != ColumnInterface::ORDER_NONE) {
                return $column;
            }
        }

        return null;
    }

    public function getSortingColumnId(): ?string
    {
        $column = $this->getSortingColumn();

        return $column[ReportInterface::COLUMN_ID] ?? null;
    }

    public function getSortingColumnExpression(): string
    {
        $column = $this->getSortingColumn();
        $expression = Select::SQL_DESC;

        if ($column) {
            if ($column[ColumnInterface::ORDER] == ColumnInterface::ORDER_ASC) {
                $expression = Select::SQL_ASC;
            }
        }

        return $expression;
    }
}
