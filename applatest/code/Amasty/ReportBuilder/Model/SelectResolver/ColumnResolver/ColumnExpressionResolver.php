<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver;

use Amasty\ReportBuilder\Model\EntityScheme\Column\AggregationType;
use Amasty\ReportBuilder\Model\ReportResolver;
use Amasty\ReportBuilder\Model\ResourceModel\Report;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolverInterface;

class ColumnExpressionResolver implements ColumnExpressionResolverInterface
{
    /**
     * @var ColumnResolverInterface
     */
    private $columnResolver;

    /**
     * @var ReportResolver
     */
    private $reportResolver;

    /**
     * @var AggregationType
     */
    private $aggregationType;

    /**
     * @var Report
     */
    private $reportResource;

    public function __construct(
        ColumnResolverInterface $columnResolver,
        ReportResolver $reportResolver,
        AggregationType $aggregationType,
        Report $reportResource
    ) {
        $this->columnResolver = $columnResolver;
        $this->reportResolver = $reportResolver;
        $this->aggregationType = $aggregationType;
        $this->reportResource = $reportResource;
    }

    public function resolve(string $columnAlias, bool $useInternal = false): string
    {
        $columnId = $this->resolveColumnId($columnAlias);
        $columns = $this->columnResolver->resolve();

        if (isset($columns[$columnId])) {
            if ($useInternal) {
                return $columns[$columnId][ColumnResolverInterface::EXPRESSION_INTERNAL];
            } else {
                return $columns[$columnId][ColumnResolverInterface::EXPRESSION];
            }
        }

        return $columnAlias;
    }

    private function resolveColumnId(string $columnAlias): string
    {
        $columns = $this->columnResolver->resolve();

        foreach ($columns as $columnId => $columnData) {
            if ($columnData[ColumnResolverInterface::ALIAS] == $columnAlias) {
                return $columnId;
            }
        }

        return $columnAlias;
    }
}
