<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnFilter;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Column\AggregationType;
use Amasty\ReportBuilder\Model\ReportResolver;
use Amasty\ReportBuilder\Model\ResourceModel\Report;
use Amasty\ReportBuilder\Model\ResourceModel\Report\Data\Select;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnAggregationTypeResolver;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnExpressionResolverInterface;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolverInterface;
use Amasty\ReportBuilder\Model\SelectResolver\Context;
use Amasty\ReportBuilder\Model\SelectResolver\EntitySimpleRelationResolver;
use Magento\Framework\Exception\LocalizedException;

class FilterApplier implements FilterApplierInterface
{
    /**
     * @var FilterResolverInterface
     */
    private $filterResolver;

    /**
     * @var ColumnResolverInterface
     */
    private $columnResolver;

    /**
     * @var EntitySimpleRelationResolver
     */
    private $simpleRelationResolver;

    /**
     * @var ColumnExpressionResolverInterface
     */
    private $columnExpressionResolver;

    /**
     * @var ReportResolver
     */
    private $reportResolver;

    /**
     * @var \Amasty\ReportBuilder\Model\EntityScheme\Provider
     */
    private $schemeProvider;

    /**
     * @var InternalFilterApplier
     */
    private $internalFilterApplier;

    /**
     * @var Report
     */
    private $reportResource;

    public function __construct(
        Context $context,
        InternalFilterApplier $internalFilterApplier,
        Report $reportResource
    ) {
        $this->filterResolver = $context->getFilterResolver();
        $this->columnResolver = $context->getColumnResolver();
        $this->simpleRelationResolver = $context->getSimpleRelationResolver();
        $this->reportResolver = $context->getReportResolver();
        $this->columnExpressionResolver = $context->getColumnExpressionResolver();
        $this->schemeProvider = $context->getEntitySchemeProvider();
        $this->internalFilterApplier = $internalFilterApplier;
        $this->reportResource = $reportResource;
    }

    public function apply(Select $select): void
    {
        $filters = $this->filterResolver->resolve();
        $connection = $this->reportResource->getConnection();

        foreach ($filters as $filter => $conditions) {
            if ($this->internalFilterApplier->apply($filter, $conditions)) {
                continue;
            }
            $whereConditions = [];
            $alias = $this->columnExpressionResolver->resolve($filter);
            foreach ($conditions as $key => $condition) {
                $whereConditions[] = $connection->prepareSqlCondition($alias, [$key => $condition]);
            }

            if ($this->getUseFilterAggregation($filter)) {
                $column = $this->resolveColumn($filter);
                if (in_array($column->getFrontendModel(), ['select', 'multiselect'])) {
                    $havingConditions = [];
                    foreach ($conditions as $condition) {
                        $havingConditions[] = sprintf(
                            'FIND_IN_SET("%s", %s)',
                            $condition,
                            $alias
                        );
                    }
                    if ($havingConditions) {
                        $select->having(implode(' OR ', $havingConditions));
                    }
                } else {
                    $select->having(implode(' AND ', $whereConditions));
                }
            } else {
                $select->where(implode(' AND ', $whereConditions));
            }
        }
    }

    private function getUseFilterAggregation(string $filter): bool
    {
        $report = $this->reportResolver->resolve();
        $columns = $this->columnResolver->resolve();

        try {
            $column = $this->resolveColumn($filter);
        } catch (LocalizedException $e) {
            return false;
        }

        $useAggregation = isset($columns[$column->getColumnId()])
            ? $columns[$column->getColumnId()][ColumnAggregationTypeResolver::USE_AGGREGATION] : false;
        return ($report->getUsePeriod() || $useAggregation)
            && $column->getAggregationType() != AggregationType::TYPE_NONE;
    }

    private function resolveColumn(string $filter): ColumnInterface
    {
        $scheme = $this->schemeProvider->getEntityScheme();
        $columns = $this->columnResolver->resolve();
        if (strpos($filter, '.') !== false) {
            $filter = str_replace('.', '_', $filter);
        }

        foreach ($columns as $columnId => $columnData) {
            if ($columnData[ColumnResolverInterface::ALIAS] == $filter) {
                return $scheme->getColumnById($columnId);
            }
        }

        throw new LocalizedException(__('Column for filter %1 does not exist', $filter));
    }
}
