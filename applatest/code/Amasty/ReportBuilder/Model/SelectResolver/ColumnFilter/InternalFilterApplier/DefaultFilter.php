<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnFilter\InternalFilterApplier;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Model\ResourceModel\Report\Data\Select;
use Amasty\ReportBuilder\Model\SelectResolver\RelationResolver;
use Amasty\ReportBuilder\Model\SelectResolver\RelationResolverInterface;

class DefaultFilter implements FilterInterface
{
    /**
     * @var RelationResolver
     */
    private $relationResolver;

    /**
     * @var PrepareWhereConditions
     */
    private $prepareWhereConditions;

    public function __construct(RelationResolver $relationResolver, PrepareWhereConditions $prepareWhereConditions)
    {
        $this->relationResolver = $relationResolver;
        $this->prepareWhereConditions = $prepareWhereConditions;
    }

    public function apply(ColumnInterface $column, array $conditions): bool
    {
        $result = false;

        $relation = $this->relationResolver->getRelationByName($column->getEntityName());
        if (isset($relation[RelationResolverInterface::TABLE])
            && $relation[RelationResolverInterface::TABLE] instanceof Select
        ) {
            $subSelect = $relation[RelationResolverInterface::TABLE];
            $whereConditionsString = $this->prepareWhereConditions->execute(
                $column->getColumnId(),
                $conditions
            );
            $subSelect->where($whereConditionsString);
            $result = true;
        }

        return $result;
    }
}
