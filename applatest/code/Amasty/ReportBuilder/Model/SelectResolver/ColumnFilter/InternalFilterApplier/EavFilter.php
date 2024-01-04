<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnFilter\InternalFilterApplier;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Model\ResourceModel\Report\Data\Select;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnBuilder\Eav\SubSelectRelationBuilder;
use Amasty\ReportBuilder\Model\SelectResolver\RelationBuilderInterface;
use Amasty\ReportBuilder\Model\SelectResolver\RelationResolver;
use Amasty\ReportBuilder\Model\SelectResolver\RelationResolverInterface;

class EavFilter implements FilterInterface
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

        $relationName = sprintf('%s_attribute', $column->getAlias());
        $relation = $this->relationResolver->getRelationByName($relationName);
        if (isset($relation[RelationResolverInterface::TABLE])
            && $relation[RelationResolverInterface::TABLE] instanceof Select
        ) {
            $relationInfo = $relation[RelationBuilderInterface::CONTENT];
            $subSelect = $relationInfo[SubSelectRelationBuilder::SELECT];
            $whereConditionsString = $this->prepareWhereConditions->execute(
                current($relationInfo[SubSelectRelationBuilder::COLUMNS]),
                $conditions
            );
            $subSelect->where($whereConditionsString);
        }

        return $result;
    }
}
