<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnModifiers\AggregationTypeModifiers;

use Amasty\ReportBuilder\Model\EntityScheme\Column\AggregationType;
use Amasty\ReportBuilder\Model\EntityScheme\Column\ColumnType;
use Amasty\ReportBuilder\Model\EntityScheme\Provider;
use Amasty\ReportBuilder\Model\ReportResolver;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnAggregationTypeResolver;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnModifiers\ColumnModifierInterface;
use Amasty\ReportBuilder\Model\SelectResolver\EntitySimpleRelationResolver;

class AggregationUsageModifier implements ColumnModifierInterface
{
    /**
     * @var Provider
     */
    private $provider;

    /**
     * @var ReportResolver
     */
    private $reportResolver;

    /**
     * @var AggregationType
     */
    private $aggregationType;

    /**
     * @var EntitySimpleRelationResolver
     */
    private $simpleRelationResolver;

    public function __construct(
        Provider $provider,
        ReportResolver $reportResolver,
        AggregationType $aggregationType,
        EntitySimpleRelationResolver $simpleRelationResolver
    ) {
        $this->provider = $provider;
        $this->reportResolver = $reportResolver;
        $this->aggregationType = $aggregationType;
        $this->simpleRelationResolver = $simpleRelationResolver;
    }

    public function modify(string $columnId, array $columnData): array
    {
        $scheme = $this->provider->getEntityScheme();
        $simpleRelations = $this->simpleRelationResolver->resolve();
        $aggregationTypes = $this->aggregationType->getSimpleAggregationsType();
        $report = $this->reportResolver->resolve();
        $schemeColumn = $scheme->getColumnById($columnId);
        $expression = $columnData[ColumnAggregationTypeResolver::AGGREGATED_EXPRESSION];
        $canAggregate = $report->getUsePeriod()
            || !in_array($schemeColumn->getEntityName(), $simpleRelations)
            || $schemeColumn->getColumnType() === ColumnType::EAV_TYPE;
        if (!$canAggregate || $expression == $aggregationTypes[AggregationType::TYPE_NONE]) {
            $columnData[ColumnAggregationTypeResolver::USE_AGGREGATION] = false;
        } else {
            $columnData[ColumnAggregationTypeResolver::USE_AGGREGATION] = true;
        }

        return $columnData;
    }
}
