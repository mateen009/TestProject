<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Report\EntityDataModifiers;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Api\EntityInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Column\AggregationType;
use Amasty\ReportBuilder\Model\EntityScheme\Provider;

class AggregationOptionsDataModifier implements EntityDataModifierInterface
{
    const AGGREGATION_OPTIONS = 'aggregationOptions';

    /**
     * @var AggregationType
     */
    private $aggregationType;

    /**
     * @var Provider
     */
    private $schemeProvider;

    public function __construct(
        Provider $schemeProvider,
        AggregationType $aggregationType
    ) {
        $this->schemeProvider = $schemeProvider;
        $this->aggregationType = $aggregationType;
    }

    public function modifyData(array $entityData): array
    {
        $scheme = $this->schemeProvider->getEntityScheme();

        if (isset($entityData[EntityInterface::COLUMNS])) {
            foreach ($entityData[EntityInterface::COLUMNS] as &$column) {
                if (isset($column[ColumnInterface::ID])) {
                    $schemeColumn = $scheme->getColumnById($column[ColumnInterface::ID]);

                    $column[self::AGGREGATION_OPTIONS] = $this->aggregationType->getOptionArray(
                        $schemeColumn->getAvailableAggregationTypes()
                    );
                }
            }
        }

        return $entityData;
    }
}
