<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Api\EntityInterface;
use Amasty\ReportBuilder\Api\RelationInterface;
use Amasty\ReportBuilder\Exception\NotExistColumnException;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class Entity extends DataObject implements EntityInterface
{
    const STORE_ID_COLUMN = 'store_id';
    const DEFAULT_COLUMN_INDEX = 0;

    /**
     * @var ColumnFactory
     */
    private $columnFactory;

    /**
     * @var RelationFactory
     */
    private $relationFactory;

    /**
     * @var ColumnInterface[]
     */
    private $columns = [];

    /**
     * @var array
     */
    private $relations = [];

    /**
     * @var array
     */
    private $expressions = [];

    public function __construct(
        ColumnFactory $columnFactory,
        RelationFactory $relationFactory,
        array $data = []
    ) {
        $this->columnFactory = $columnFactory;
        $this->relationFactory = $relationFactory;

        if (isset($data['config'])) {
            $this->init($data['config']);
        }

        parent::__construct($data);
    }

    /**
     * @param array $entityConfig
     * @throws LocalizedException
     */
    public function init(array $entityConfig): void
    {
        $this->validateEntityConfig($entityConfig);
        $this->reset();
        $this->setName($entityConfig[EntityInterface::NAME]);
        $this->setTitle($entityConfig[EntityInterface::TITLE]);
        $this->setMainTable($entityConfig[EntityInterface::MAIN_TABLE]);
        $this->setPrimaryFlag((bool) ($entityConfig[EntityInterface::PRIMARY] ?? false));
        $this->setHiddenFlag($entityConfig[EntityInterface::HIDDEN] ?? false);
        foreach ($entityConfig[EntityInterface::COLUMNS] as $columnName => $columnConfig) {
            $this->addColumn($columnName, $columnConfig);
        }

        foreach ($entityConfig[EntityInterface::RELATIONS] as $relationName => $relationConfig) {
            $this->addRelation($relationName, $relationConfig);
        }

        if (isset($entityConfig[EntityInterface::EXPRESSIONS])) {
            $this->setExpressions($entityConfig[EntityInterface::EXPRESSIONS]);
        }
    }

    /**
     * @param array $entityConfig
     * @throws LocalizedException
     */
    private function validateEntityConfig(array $entityConfig): void
    {
        if (!$this->isDataValid($entityConfig, EntityInterface::NAME)) {
            throw new LocalizedException(__('Name is required field for entity'));
        }

        if (!$this->isDataValid($entityConfig, EntityInterface::TITLE)) {
            throw new LocalizedException(__('Title is required field for entity'));
        }

        if (!$this->isDataValid($entityConfig, EntityInterface::MAIN_TABLE)) {
            throw new LocalizedException(__('Main Table is required field for entity'));
        }

        if (!$this->isDataValid($entityConfig, EntityInterface::COLUMNS)) {
            throw new LocalizedException(__('Entity must have at least one column'));
        }
    }

    /**
     * @param string $columnName
     * @param array $columnConfig
     * @return ColumnInterface
     * @throws LocalizedException
     */
    public function addColumn(string $columnName, array $columnConfig): ColumnInterface
    {
        if (isset($this->columns[$columnName])) {
            throw new LocalizedException(__('Column %1 already exists', $columnName));
        }

        $column = $this->columnFactory->create();
        $column->init($columnConfig);
        $column->setEntityName($this->getName());
        $this->columns[$columnName] = $column;

        return $column;
    }

    /**
     * @param string $columnName
     * @return ColumnInterface
     * @throws LocalizedException
     */
    public function getColumn(string $columnName): ColumnInterface
    {
        if (!isset($this->columns[$columnName])) {
            throw new NotExistColumnException(__('Column %1 does not exist', $columnName));
        }

        return $this->columns[$columnName];
    }

    public function getColumnIndex(string $columnName): int
    {
        if (isset($this->columns[$columnName])) {
            return (int) array_search($columnName, array_keys($this->columns));
        }

        return self::DEFAULT_COLUMN_INDEX;
    }

    /**
     * @return ColumnInterface
     * @throws LocalizedException
     */
    public function getPrimaryColumn(): ColumnInterface
    {
        foreach ($this->columns as $column) {
            if ($column->getPrimary()) {
                return $column;
            }
        }

        throw new LocalizedException(__('Entity %1 does not have primary column', $this->getTitle()));
    }

    /**
     * @return ColumnInterface
     * @throws LocalizedException
     */
    public function getPeriodColumn(): ColumnInterface
    {
        foreach ($this->columns as $column) {
            if ($column->getUseForPeriod()) {
                return $column;
            }
        }

        throw new LocalizedException(__('Entity %1 does not have datetime column', $this->getTitle()));
    }

    public function addRelation(string $relationName, array $relationConfig): RelationInterface
    {
        if (isset($this->relations[$relationName])) {
            throw new LocalizedException(__('Relation %1 already exists', $relationName));
        }

        $relation = $this->relationFactory->create();
        $relation->init($relationConfig);
        $this->relations[$relationName] = $relation;

        return $relation;
    }

    /**
     * @param string $relatedEntityName
     * @return RelationInterface
     * @throws LocalizedException
     */
    public function getRelation(string $relatedEntityName): RelationInterface
    {
        if (!isset($this->relations[$relatedEntityName])) {
            throw new LocalizedException(__('Relation to %1 does not exist', $relatedEntityName));
        }

        return $this->relations[$relatedEntityName];
    }

    public function getRelatedEntities(): array
    {
        return array_keys($this->relations);
    }

    public function setExpressions(array $expressions): void
    {
        foreach ($expressions as $name => $expression) {
            $this->addExpression($name, $expression);
        }
    }

    public function addExpression(string $name, string $expression): void
    {
        $this->expressions[$name] = $expression;
    }

    public function getExpressions(): array
    {
        return $this->expressions;
    }

    private function reset(): void
    {
        $this->columns = [];
        $this->relations = [];
        $this->expressions = [];
        $this->setExpressions([]);
    }

    private function isDataValid(array $data, string $key): bool
    {
        return isset($data[$key]) && !empty($data[$key]);
    }

    /**
     * @param array $keys
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray(array $keys = []): array
    {
        return array_merge(parent::toArray(), [
            EntityInterface::COLUMNS => $this->prepareColumnsArray($this->columns)
        ]);
    }

    public function getRelations(): array
    {
        return $this->relations;
    }

    private function prepareColumnsArray(array $columns): array
    {
        $result = [];
        foreach ($columns as $name => $column) {
            $columnData = $column->toArray();
            $columnData[ColumnInterface::ID] = sprintf('%s.%s', $this->getName(), $name);
            $columnData[ColumnInterface::ENTITY_NAME] = $this->getName();
            $result[$name] = $columnData;
        }

        return $result;
    }

    public function setTitle(string $title): void
    {
        $this->setData(EntityInterface::TITLE, $title);
    }

    public function getTitle(): string
    {
        return (string) $this->getData(EntityInterface::TITLE);
    }

    public function setName(string $name): void
    {
        $this->setData(EntityInterface::NAME, $name);
    }

    public function getName(): string
    {
        return (string) $this->getData(EntityInterface::NAME);
    }

    public function setMainTable(string $tableName): void
    {
        $this->setData(EntityInterface::MAIN_TABLE, $tableName);
    }

    public function getMainTable(): string
    {
        return (string) $this->getData(EntityInterface::MAIN_TABLE);
    }

    public function isStoreColumnExist(): bool
    {
        $result = false;
        foreach (array_keys($this->columns) as $name) {
            if ($name == self::STORE_ID_COLUMN) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    public function setPrimaryFlag(bool $primary = false): void
    {
        $this->setData(self::PRIMARY, $primary);
    }

    public function isPrimary(): bool
    {
        return (bool) $this->getData(self::PRIMARY);
    }

    public function setHiddenFlag(bool $isHidden): void
    {
        $this->setData(self::HIDDEN, $isHidden);
    }

    public function isHidden(): bool
    {
        return (bool) $this->getData(self::HIDDEN);
    }
}
