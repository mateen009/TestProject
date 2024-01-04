<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Api\EntityInterface;
use Amasty\ReportBuilder\Api\EntityScheme\SchemeInterface;
use Amasty\ReportBuilder\Exception\NotExistColumnException;
use Amasty\ReportBuilder\Exception\NotExistTableException;
use Amasty\ReportBuilder\Model\EntityScheme\Column\ColumnType;
use Amasty\ReportBuilder\Model\EntityScheme\Relation\Type;
use Magento\Framework\Exception\LocalizedException;
use Amasty\ReportBuilder\Model\EntityScheme\EntityFactory;

class EntityScheme implements SchemeInterface
{
    /**
     * @var EntityFactory
     */
    private $entityFactory;

    /**
     * @var array
     */
    private $schemeData = [];

    /**
     * @var array
     */
    private $entities = [];

    public function __construct(EntityFactory $entityFactory)
    {
        $this->entityFactory = $entityFactory;
    }

    public function init(array $schemeConfiguration): void
    {
        $this->schemeData = $schemeConfiguration;
    }

    /**
     * @param string $entityName
     * @return EntityInterface|null
     * @throws LocalizedException
     */
    public function getEntityByName(string $entityName): ?EntityInterface
    {
        if (!isset($this->entities[$entityName])) {
            if (!isset($this->schemeData[$entityName])) {
                throw new NotExistTableException(__('Entity %1 does not exist', $entityName));
            }

            $this->addEntity($entityName, $this->schemeData[$entityName]);
        }

        return $this->entities[$entityName] ?? null;
    }

    /**
     * @param string $columnId
     * @return ColumnInterface|null
     * @throws LocalizedException
     */
    public function getColumnById(string $columnId): ?ColumnInterface
    {
        [$entityName, $columnName] = explode('.', $columnId);

        if (!$entityName || !$columnName) {
            throw new NotExistColumnException(__('Requested column does not exist'));
        }

        $entity = $this->getEntityByName($entityName);
        $column = $entity ? $entity->getColumn($columnName) : null;
        if ($column && $column->getColumnType() === ColumnType::FOREIGN_TYPE) {
            $column->setParentColumn($this->getColumnById($column->getLink()));
        }

        return $column;
    }

    /**
     * @param string $entityName
     * @param array $config
     * @return EntityInterface
     * @throws LocalizedException
     */
    public function addEntity(string $entityName, array $config): EntityInterface
    {
        if (isset($this->entities[$entityName])) {
            throw new LocalizedException(__('Entity %1 aready exists', $entityName));
        }

        $entity = $this->entityFactory->create();
        $entity->init($config);

        $this->entities[$entityName] = $entity;

        return $entity;
    }

    public function getAllEntitiesOptionArray(bool $primariesOnly = false): array
    {
        $result = [];
        foreach ($this->schemeData as $entityName => $entityData) {
            $isPrimary = isset($entityData[EntityInterface::PRIMARY]) && $entityData[EntityInterface::PRIMARY];
            if ($primariesOnly && !$isPrimary) {
                continue;
            }
            $result[$entityName] = $entityData[EntityInterface::TITLE];
        }

        return $result;
    }

    public function getEntitiesCollection(): array
    {
        $result = [];

        foreach ($this->getAllEntitiesOptionArray() as $entityName => $title) {
            $result[$entityName] = $this->getEntityByName($entityName);
        }

        return $result;
    }

    public function getSimpleRelations(string $mainEntityName, array $simpleRelations = []): array
    {
        if (!in_array($mainEntityName, $simpleRelations)) {
            $simpleRelations[] = $mainEntityName;
        }

        $mainEntity = $this->getEntityByName($mainEntityName);

        foreach ($this->getEntitiesCollection() as $entity) {

            if (in_array($mainEntity->getName(), $simpleRelations)
                && !in_array($entity->getName(), $simpleRelations)
            ) {
                try {
                    $relation = $mainEntity->getRelation($entity->getName());
                } catch (LocalizedException $e) {
                    continue;
                }

                if ($relation->getRelationshipType() == Type::ONE_TO_ONE) {
                    $simpleRelations = $this->getSimpleRelations($entity->getName(), $simpleRelations);
                }
            }
        }

        return $simpleRelations;
    }
}
