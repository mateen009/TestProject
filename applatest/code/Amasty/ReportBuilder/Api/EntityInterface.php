<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Api;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Api\RelationInterface;
use Magento\Framework\Data\OptionSourceInterface;

interface EntityInterface
{
    const TITLE = 'title';
    const NAME = 'name';
    const MAIN_TABLE = 'main_table';
    const COLUMNS = 'columns';
    const RELATIONS = 'relations';
    const EXPRESSIONS = 'expressions';
    const EAV = 'eav';
    const PRIMARY = 'primary';
    const USE_AGGREGATION = 'use_aggregation';
    const HIDDEN = 'hidden';

    /**
     * Add column to collection
     *
     * @param string $columnName
     * @param array $config
     * @return \Amasty\ReportBuilder\Api\ColumnInterface
     */
    public function addColumn(string $columnName, array $config): ColumnInterface;

    /**
     * Add relation to collection
     *
     * @param string $relationName
     * @param array $relationConfig
     * @return \Amasty\ReportBuilder\Api\RelationInterface
     */
    public function addRelation(string $relationName, array $relationConfig): RelationInterface;

    /**
     * Get column by name
     *
     * @param string $columnName
     * @return \Amasty\ReportBuilder\Api\ColumnInterface
     */
    public function getColumn(string $columnName): ColumnInterface;

    /**
     * Get column index by name
     *
     * @param string $columnName
     * @return int
     */
    public function getColumnIndex(string $columnName): int;

    /**
     * Get primary column
     *
     * @return \Amasty\ReportBuilder\Api\ColumnInterface
     */
    public function getPrimaryColumn(): ColumnInterface;

    /**
     * Get datetime column for period
     *
     * @return \Amasty\ReportBuilder\Api\ColumnInterface
     */
    public function getPeriodColumn(): ColumnInterface;

    /**
     * Get relation by name
     *
     * @param string $relatedEntityName
     * @return \Amasty\ReportBuilder\Api\RelationInterface
     */
    public function getRelation(string $relatedEntityName): RelationInterface;

    /**
     * Method returns an array of entity names related to current entity
     *
     * @return array
     */
    public function getRelatedEntities(): array;

    /**
     * @return RelationInterface[]
     */
    public function getRelations(): array;

    /**
     * Method uses for initialization Entity object from array
     *
     * @param array $entityConfig
     */
    public function init(array $entityConfig): void;

    public function setTitle(string $title): void;

    public function getTitle(): string;

    public function setName(string $name): void;

    public function getName(): string;

    public function setMainTable(string $tableName): void;

    public function getMainTable(): string;

    public function setExpressions(array $expressions): void;

    public function getExpressions(): array;

    public function addExpression(string $name, string $expression): void;

    public function isStoreColumnExist(): bool;

    public function setPrimaryFlag(bool $primary = false): void;

    public function isPrimary(): bool;

    public function isHidden(): bool;
}
