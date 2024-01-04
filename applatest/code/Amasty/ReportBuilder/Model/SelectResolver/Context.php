<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver;

use Amasty\ReportBuilder\Model\EntityScheme\Provider;
use Amasty\ReportBuilder\Model\ReportResolver;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnFilter\FilterResolverInterface;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnAggregationTypeResolver;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnExpressionResolverInterface;

class Context
{
    /**
     * @var ReportResolver
     */
    private $reportResolver;

    /**
     * @var Provider
     */
    private $schemeProvider;

    /**
     * @var ColumnResolverInterface
     */
    private $columnResolver;

    /**
     * @var ColumnExpressionResolverInterface
     */
    private $columnExpressionResolver;

    /**
     * @var EntitySimpleRelationResolver
     */
    private $simpleRelationResolver;

    /**
     * @var FilterResolverInterface
     */
    private $filterResolver;

    /**
     * @var ColumnAggregationTypeResolver
     */
    private $columnAggregationTypeResolver;

    public function __construct(
        ReportResolver $reportResolver,
        Provider $schemeProvider,
        ColumnResolverInterface $columnResolver,
        FilterResolverInterface $filterResolver,
        ColumnExpressionResolverInterface $columnExpressionResolver,
        EntitySimpleRelationResolver $simpleRelationResolver,
        ColumnAggregationTypeResolver $columnAggregationTypeResolver
    ) {
        $this->reportResolver = $reportResolver;
        $this->schemeProvider = $schemeProvider;
        $this->columnResolver = $columnResolver;
        $this->filterResolver = $filterResolver;
        $this->columnExpressionResolver = $columnExpressionResolver;
        $this->simpleRelationResolver = $simpleRelationResolver;
        $this->columnAggregationTypeResolver = $columnAggregationTypeResolver;
    }

    public function getReportResolver(): ReportResolver
    {
        return $this->reportResolver;
    }

    public function getEntitySchemeProvider(): Provider
    {
        return $this->schemeProvider;
    }

    public function getColumnResolver(): ColumnResolverInterface
    {
        return $this->columnResolver;
    }

    public function getColumnExpressionResolver(): ColumnExpressionResolverInterface
    {
        return $this->columnExpressionResolver;
    }

    public function getSimpleRelationResolver(): EntitySimpleRelationResolver
    {
        return $this->simpleRelationResolver;
    }

    public function getFilterResolver(): FilterResolverInterface
    {
        return $this->filterResolver;
    }
}
