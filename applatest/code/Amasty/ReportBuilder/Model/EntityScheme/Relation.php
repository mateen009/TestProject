<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme;

use Amasty\ReportBuilder\Api\RelationInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Relation\JoinType;
use Amasty\ReportBuilder\Model\EntityScheme\Relation\Type;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class Relation extends DataObject implements RelationInterface
{
    public function __construct(array $data = [])
    {
        if (isset($data['config'])) {
            $this->init($data['config']);
            unset($data['config']);
        }

        parent::__construct($data);
    }

    public function init(array $relationConfig): void
    {
        $this->validateConfig($relationConfig);

        if (!isset($relationConfig[RelationInterface::TYPE])) {
            $relationConfig[RelationInterface::TYPE] = Type::TYPE_COLUMN;
        }
        if (!isset($relationConfig[RelationInterface::JOIN_TYPE])) {
            $relationConfig[RelationInterface::JOIN_TYPE] = JoinType::INNER_JOIN;
        }

        $this->setData($relationConfig);
    }

    private function validateConfig(array $relationConfig): void
    {
        if (!isset($relationConfig[RelationInterface::NAME])) {
            throw new LocalizedException(__('Name is required field for Relation'));
        }

        if (!isset($relationConfig[RelationInterface::COLUMN])) {
            throw new LocalizedException(__('Column is required field for Relation'));
        }

        if (!isset($relationConfig[RelationInterface::REFERENCE_COLUMN])) {
            throw new LocalizedException(__('Reference Column is required field for Relation'));
        }
    }

    public function setName(string $name): void
    {
        $this->setData(RelationInterface::NAME, $name);
    }

    public function getName(): string
    {
        return $this->getData(RelationInterface::NAME);
    }

    public function setType(string $type): void
    {
        $this->setData(RelationInterface::TYPE, $type);
    }

    public function getType(): string
    {
        return $this->getData(RelationInterface::TYPE) ?? Type::TYPE_COLUMN;
    }

    public function setColumn(string $columnName): void
    {
        $this->setData(RelationInterface::COLUMN, $columnName);
    }

    public function getColumn(): string
    {
        return $this->getData(RelationInterface::COLUMN);
    }

    public function setReferenceColumn(string $columnName): void
    {
        $this->setData(RelationInterface::REFERENCE_COLUMN, $columnName);
    }

    public function getReferenceColumn(): string
    {
        return $this->getData(RelationInterface::REFERENCE_COLUMN);
    }

    public function setRelationshipType(string $type): void
    {
        $this->setData(RelationInterface::RELATIONSHIP_TYPE, $type);
    }

    public function getRelationshipType(): ?string
    {
        return $this->getData(RelationInterface::RELATIONSHIP_TYPE);
    }

    public function setRelationTable(string $tableName): void
    {
        $this->setData(RelationInterface::RELATION_TABLE, $tableName);
    }

    public function getRelationTable(): ?string
    {
        return $this->getData(RelationInterface::RELATION_TABLE);
    }

    public function setRelationColumn(string $columnName): void
    {
        $this->setData(RelationInterface::RELATION_COLUMN, $columnName);
    }

    public function getRelationColumn(): ?string
    {
        return $this->getData(RelationInterface::RELATION_COLUMN);
    }

    public function setRelationReferenceColumn(string $columnName): void
    {
        $this->setData(RelationInterface::RELATION_REFERENCE_COLUMN, $columnName);
    }

    public function getRelationReferenceColumn(): ?string
    {
        return $this->getData(RelationInterface::RELATION_REFERENCE_COLUMN);
    }

    public function setJoinType(string $joinType): void
    {
        $this->setData(RelationInterface::JOIN_TYPE, $joinType);
    }

    public function getJoinType(): ?string
    {
        return $this->getData(RelationInterface::JOIN_TYPE);
    }
}
