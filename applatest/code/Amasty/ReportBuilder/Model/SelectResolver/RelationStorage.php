<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Model\ResourceModel\Report\Data\Select;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnModifiers;

class RelationStorage implements RelationStorageInterface
{
    /**
     * @var array
     */
    private $relations = [];

    public function init(): void
    {
        $this->relations = [];
    }

    public function addRelation(array $relationConfig): void
    {
        $this->relations[$relationConfig[RelationResolverInterface::ALIAS]] = $relationConfig;
    }

    public function getAllRelations(): array
    {
        return $this->relations;
    }

    public function setRelations(array $relations): void
    {
        foreach ($relations as $relation) {
            $this->addRelation($relation);
        }
    }
}
