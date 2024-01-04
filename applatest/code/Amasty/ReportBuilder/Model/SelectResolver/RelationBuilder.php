<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver;

use Amasty\ReportBuilder\Model\ResourceModel\Report\Data\Select;

class RelationBuilder implements RelationBuilderInterface
{
    /**
     * @var array
     */
    private $pool;

    /**
     * @var RelationResolverInterface
     */
    private $relationResolver;

    /**
     * @var RelationStorageInterface
     */
    private $relationStorage;

    public function __construct(
        RelationResolverInterface $relationResolver,
        RelationStorageInterface $relationStorage,
        array $pool = []
    ) {
        $this->pool = $pool;
        $this->relationResolver = $relationResolver;
        $this->relationStorage = $relationStorage;
    }

    public function build(Select $select): void
    {
        $this->relationStorage->init();
        $relations = $this->relationResolver->resolve();

        foreach ($this->pool as $modifier) {
            $relations = $modifier->modify($relations);
        }

        $this->buildRelations($select, $relations);
        $this->relationStorage->setRelations($relations);
    }

    private function buildRelations(Select $select, array $relations): array
    {
        $processedRelations = [];

        foreach ($relations as $relation) {
            if (in_array($relation[self::ALIAS], $processedRelations)) {
                continue;
            }
            $select->joinByType(
                $relation[self::TYPE],
                [$relation[self::ALIAS] => $relation[self::TABLE]],
                $relation[self::CONDITION],
                $relation[self::COLUMNS] ?? []
            );
            $children = $this->getChildrenRelations($relation, $relations);
            if ($children) {
                $parentSelect = $relation[RelationBuilderInterface::TABLE] instanceof Select ?
                    $relation[RelationBuilderInterface::TABLE] : $select;
                // phpcs:ignore
                $processedRelations = array_merge($processedRelations, $this->buildRelations($parentSelect, $children));
            }

            $processedRelations[] = $relation[self::ALIAS];
        }

        return $processedRelations;
    }

    private function getChildrenRelations(array $parentRelation, array $relations): array
    {
        $children = [];
        foreach ($relations as $relation) {
            if ($relation[RelationBuilder::PARENT] == $parentRelation[RelationBuilder::ALIAS]) {
                $children[] = $relation;
                // phpcs:ignore
                $children = array_merge($children, $this->getChildrenRelations($relation, $relations));
            }
        }

        return $children;
    }
}
