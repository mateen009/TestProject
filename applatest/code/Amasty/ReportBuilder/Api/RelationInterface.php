<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Api;

interface RelationInterface
{
    const SCHEME_ROUTING_TABLE = 'amasty_report_builder_scheme_relation';
    const NAME = 'name';
    const RELATIONSHIP_TYPE = 'relationship_type';
    const COLUMN = 'column';
    const REFERENCE_COLUMN = 'reference_column';
    const TYPE = 'type';
    const RELATION_TABLE = 'relation_table';
    const RELATION_COLUMN = 'relation_column';
    const RELATION_REFERENCE_COLUMN = 'relation_reference_column';
    const JOIN_TYPE = 'join_type';

    /**
     * Method uses for initialization Entity object from array
     *
     * @param array $relationConfig
     */
    public function init(array $relationConfig): void;

    public function setName(string $name): void;

    public function getName(): string;

    public function setType(string $type): void;

    public function getType(): string;

    public function setColumn(string $columnName): void;

    public function getColumn(): string;

    public function setReferenceColumn(string $columnName): void;

    public function getReferenceColumn(): string;

    public function setRelationshipType(string $type): void;

    public function getRelationshipType(): ?string;

    public function setRelationTable(string $tableName): void;

    public function getRelationTable(): ?string;

    public function setRelationColumn(string $columnName): void;

    public function getRelationColumn(): ?string;

    public function setRelationReferenceColumn(string $columnName): void;

    public function getRelationReferenceColumn(): ?string;

    public function setJoinType(string $joinType): void;

    public function getJoinType(): ?string;
}
