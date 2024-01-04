<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Api;

use Magento\Framework\Data\OptionSourceInterface;

interface ColumnInterface
{
    const COLUMN_TABLE = 'amasty_report_builder_column';
    const ID = 'id';
    const ENTITY_NAME = 'entity_name';
    const TITLE = 'title';
    const NAME = 'name';
    const PRIMARY = 'primary';
    const TYPE = 'type';
    const SOURCE_MODEL = 'source_model';
    const OPTIONS = 'options';
    const AGGREGATION_TYPE = 'aggregation_type';
    const IS_DATE_FILTER = 'is_date_filter';
    const ORDER = 'order';
    const VISIBILITY = 'visibility';
    const POSITION = 'position';
    const CUSTOM_TITLE = 'custom_title';
    const FILTER = 'filter';
    const USE_FOR_PERIOD = 'use_for_period';
    const USE_FOR_PERIOD_ATTRIBUTE = 'useForPeriod';
    const FRONTEND_MODEL = 'frontend_model';
    const FRONTEND_INPUT = 'frontend_input';
    const FRONTEND_MODEL_ATTRIBUTE = 'frontendModel';
    const ATTRIBUTE_ID = 'attribute_id';
    const HIDDEN = 'hidden';
    const COLUMN_TYPE = 'column_type';
    const LINK = 'link';
    const PARENT_COLUMN = 'parent_column';
    const CUSTOM_EXPRESSION = 'custom_expression';

    const ORDER_NONE = 0;
    const ORDER_ASC = 1;
    const ORDER_DESC = 2;

    /**
     * Method uses for initialization Column object from array
     *
     * @param array $columnConfig
     */
    public function init(array $columnConfig): void;

    public function setTitle(string $title): void;

    public function getTitle(): string;

    public function getCustomTitle(): string;

    public function setCustomTitle(string $customTitle): void;

    public function setName(string $name): void;

    public function getName(): string;

    public function setType(string $type): void;

    public function getType(): string;

    public function setSourceModel(string $sourceModel): void;

    public function getSourceModel(): ?string;

    public function setOptions(array $options): void;

    public function getOptions(): ?array;

    public function setAggregationType(string $aggregationType): void;

    public function getAggregationType(): string;

    public function getPrimary(): bool;

    public function setPrimary(bool $primary): void;

    public function getSource(): OptionSourceInterface;

    public function getFrontendModel(): string;

    public function setFrontendModel(string $frontendModel): void;

    public function setUseForPeriod(bool $useForPeriod): void;

    public function getUseForPeriod(): bool;

    public function getPosition(): int;

    public function setPosition(int $position): void;

    public function getEntityName(): ?string;

    public function setEntityName(string $entityName): void;

    public function getAlias(): string;

    public function getAttributeId(): ?int;

    public function getColumnId(): string;

    public function getAvailableAggregationTypes(): array;

    public function getColumnType(): string;

    public function setColumnType(string $columnType): void;

    public function setLink(string $link): void;

    public function getLink(): string;

    public function getParentColumn(): ?ColumnInterface;

    public function setParentColumn(ColumnInterface $column): void;

    public function setCustomExpression(string $customExpression): void;

    public function getCustomExpression(): string;
}
