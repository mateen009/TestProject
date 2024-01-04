<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnModifiers\AggregationTypeModifiers;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Provider;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnModifiers\ColumnModifierInterface;

class AggregationTypeModifier implements ColumnModifierInterface
{
    /**
     * @var Provider
     */
    private $provider;

    public function __construct(Provider $provider)
    {
        $this->provider = $provider;
    }

    public function modify(string $columnId, array $columnData): array
    {
        $column = $this->provider->getEntityScheme()->getColumnById($columnId);
        if (isset($columnData[ColumnInterface::AGGREGATION_TYPE])
            && !empty($columnData[ColumnInterface::AGGREGATION_TYPE])
        ) {
            $column->setAggregationType((string) $columnData[ColumnInterface::AGGREGATION_TYPE]);
        }

        return $columnData;
    }
}
