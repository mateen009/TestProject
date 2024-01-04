<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnFilter;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Model\ReportResolver;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnFilter\InternalFilterApplier\FilterInterface;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnAggregationTypeResolver;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolverInterface;
use Amasty\ReportBuilder\Model\SelectResolver\Context;
use Amasty\ReportBuilder\Model\SelectResolver\EntitySimpleRelationResolver;
use Amasty\ReportBuilder\Model\SelectResolver\RelationResolver;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InternalFilterApplier
{
    /**
     * @var ColumnResolverInterface
     */
    private $columnResolver;

    /**
     * @var EntitySimpleRelationResolver
     */
    private $simpleRelationResolver;

    /**
     * @var ReportResolver
     */
    private $reportResolver;

    /**
     * @var \Amasty\ReportBuilder\Model\EntityScheme\Provider
     */
    private $schemeProvider;

    /**
     * @var RelationResolver
     */
    private $relationResolver;

    /**
     * @var FilterInterface[]
     */
    private $filtersPool;

    public function __construct(
        Context $context,
        RelationResolver $relationResolver,
        array $filtersPool = []
    ) {
        $this->columnResolver = $context->getColumnResolver();
        $this->simpleRelationResolver = $context->getSimpleRelationResolver();
        $this->reportResolver = $context->getReportResolver();
        $this->schemeProvider = $context->getEntitySchemeProvider();
        $this->relationResolver = $relationResolver;
        $this->filtersPool = $filtersPool;
    }

    public function apply(string $filter, array $conditions): bool
    {
        $column = $this->resolveColumn($filter);
        if ($column === null) {
            return false;
        }

        if (!$this->getUseInternalFilterAggregation($column)) {
            return false;
        }

        $filter = $this->filtersPool[$column->getColumnType()];

        return $filter->apply($column, $conditions);
    }

    private function getUseInternalFilterAggregation(ColumnInterface $column): bool
    {
        $report = $this->reportResolver->resolve();
        $simpleRelations = $this->simpleRelationResolver->resolve();
        $columns = $this->columnResolver->resolve();
        if (($report->getUsePeriod() || !in_array($column->getEntityName(), $simpleRelations))
            && !$columns[$column->getColumnId()][ColumnAggregationTypeResolver::USE_AGGREGATION]
        ) {
            return true;
        }

        return false;
    }

    private function resolveColumn(string $filter): ?ColumnInterface
    {
        foreach ($this->columnResolver->resolve() as $columnId => $columnData) {
            if ($filter == $columnId || $filter == $columnData[ColumnResolverInterface::ALIAS]) {
                return $this->schemeProvider->getEntityScheme()->getColumnById($columnId);
            }
        }

        return null;
    }
}
