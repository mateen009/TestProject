<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Api\Data;

interface ReportInterface
{
    const MAIN_TABLE = 'amasty_report_builder_report';
    const REPORT_ID = 'report_id';
    const NAME = 'name';
    const MAIN_ENTITY = 'main_entity';
    const STORE_IDS = 'store_ids';
    const USE_PERIOD = 'is_use_period';
    const DISPLAY_CHART = 'display_chart';
    const CHART_AXIS_X = 'chart_axis_x';
    const CHART_AXIS_Y = 'chart_axis_y';
    const COLUMN_ID = 'column_id';
    const SCHEME_ENTITY = 'entity';
    const SCHEME_SOURCE_ENTITY = 'source_entity';
    const COLUMNS = 'columns';
    const REPORT_COLUMN_ID = 'column_id';
    const REPORT_COLUMN_FILTER = 'filter';
    const SCHEME = 'scheme';

    const PERSIST_NAME = 'amasty_reportbuilder_report';

    public function getReportId(): int;

    public function setReportId(?int $reportId): void;

    public function getName(): ?string;

    public function setName(string $name): void;

    public function getMainEntity(): ?string;

    public function setMainEntity(string $entity): void;

    public function getStoreIds(): array;

    public function setStoreIds(array $storeIds = []): void;

    public function getUsePeriod(): bool;

    public function setUsePeriod(bool $usePeriod = false): void;

    public function getDisplayChart(): bool;

    public function setDisplayChart(bool $displayChart = false): void;

    public function getChartAxisX(): ?string;

    public function setChartAxisX(string $columnId): void;

    public function getChartAxisY(): ?string;

    public function setChartAxisY(string $columnId): void;

    public function setColumns(array $columns): void;

    public function getAllColumns(): ?array;

    public function setRelationScheme(array $scheme): void;

    public function getRelationScheme(): ?array;

    public function getAllFilters(): array;

    public function getAllEntities(): array;

    public function getSortingColumnId(): ?string;

    public function getSortingColumnExpression(): string;
}
