<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnModifiers\ExpressionModifiers;

use Amasty\ReportBuilder\Model\EntityScheme\Column\AggregationType;
use Amasty\ReportBuilder\Model\EntityScheme\Provider;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnAggregationTypeResolver;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolverInterface;

class SimpleAggregationModifier implements ModifierInterface
{
    /**
     * @var Provider
     */
    private $provider;

    /**
     * @var AggregationType
     */
    private $aggregationType;

    public function __construct(
        Provider $provider,
        AggregationType $aggregationType
    ) {
        $this->provider = $provider;
        $this->aggregationType = $aggregationType;
    }

    public function modify(string $columnId, array $selectColumnData): array
    {
        if (isset($selectColumnData[ColumnAggregationTypeResolver::AGGREGATED_EXPRESSION])
            && $selectColumnData[ColumnAggregationTypeResolver::USE_AGGREGATION]
        ) {
            $schemeColumn = $this->provider->getEntityScheme()->getColumnById($columnId);

            $parentExpression = $this->aggregationType->getParentAggregationExpression($schemeColumn);
            $selectColumnData[ColumnResolverInterface::EXPRESSION] = sprintf(
                $parentExpression,
                $selectColumnData[ColumnResolverInterface::EXPRESSION]
            );
            $selectColumnData[ColumnResolverInterface::EXPRESSION_INTERNAL] = sprintf(
                $selectColumnData[ColumnAggregationTypeResolver::AGGREGATED_EXPRESSION],
                $selectColumnData[ColumnResolverInterface::EXPRESSION_INTERNAL]
            );
        }

        return $selectColumnData;
    }
}
