<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnModifiers\AggregationTypeModifiers;

use Amasty\ReportBuilder\Model\EntityScheme\Provider;
use Amasty\ReportBuilder\Model\EntityScheme\Column\AggregationType;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnAggregationTypeResolver;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnModifiers\ColumnModifierInterface;

class SimpleAggregationModifier implements ColumnModifierInterface
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

    public function modify(string $columnId, array $columnData): array
    {
        $simpleAggregationsType = $this->aggregationType->getSimpleAggregationsType();
        $schemeColumn = $this->provider->getEntityScheme()->getColumnById($columnId);
        $aggregationType = $schemeColumn->getAggregationType();
        if (array_key_exists($aggregationType, $simpleAggregationsType)) {
            $columnData[ColumnAggregationTypeResolver::AGGREGATED_EXPRESSION] =
                $simpleAggregationsType[$aggregationType];
        }

        return $columnData;
    }
}
