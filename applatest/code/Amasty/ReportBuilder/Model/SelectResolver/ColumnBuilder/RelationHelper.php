<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnBuilder;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Model\ResourceModel\Report\Data\Select;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolverInterface;
use Amasty\ReportBuilder\Model\SelectResolver\Context;
use Amasty\ReportBuilder\Model\SelectResolver\RelationResolverInterface;

class RelationHelper
{
    const MAX_RELATION_ITERATION_COUNT = 50;

    /**
     * @var \Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnExpressionResolverInterface
     */
    private $columnExpressionResolver;

    /**
     * @var RelationResolverInterface
     */
    private $relationResolver;

    public function __construct(
        Context $context,
        RelationResolverInterface $relationResolver
    ) {
        $this->columnExpressionResolver = $context->getColumnExpressionResolver();
        $this->relationResolver = $relationResolver;
    }

    public function getParentSubSelectRelation(array $currentRelation, array $relations): ?array
    {
        $parentName = $currentRelation[RelationResolverInterface::PARENT];
        if (isset($relations[$parentName])) {
            $parent = $relations[$parentName];
            if ($parent[RelationResolverInterface::TABLE] instanceof Select) {
                return $parent;
            } else {
                return $this->getParentSubSelectRelation($parent, $relations);
            }
        }

        return null;
    }

    public function throwRelations(Select $select, array $columnData, array $relation): void
    {
        $expression = $columnData[ColumnResolverInterface::EXPRESSION];
        $relations = $this->relationResolver->resolve();

        $i = 0;
        while ($i++ < self::MAX_RELATION_ITERATION_COUNT) {
            $previousRelation = $relation;

            $relation = $this->getParentSubSelectRelation($relation, $relations);
            if ($relation) {
                $subSelect = $relation[RelationResolverInterface::TABLE];
                $subSelect->columns(
                    [$columnData[ColumnResolverInterface::ALIAS] => $expression],
                    $previousRelation[RelationResolverInterface::ALIAS]
                );
            } else {
                $select->columns(
                    [$columnData[ColumnResolverInterface::ALIAS] => $expression],
                    $previousRelation[RelationResolverInterface::ALIAS]
                );
                break;
            }
        }
    }

    public function isColumnInSelect(Select $select, ColumnInterface $column): bool
    {
        $columns = $select->getPart(Select::COLUMNS);
        foreach ($columns as $columnData) {
            if (isset($columnData[2]) && $columnData[2] == $column->getAlias()) {
                return true;
            }
        }

        return false;
    }
}
