<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver;

interface RelationStorageInterface
{
    public function init(): void;

    public function addRelation(array $relationConfig): void;

    public function getAllRelations(): array;

    public function setRelations(array $relations): void;
}
