<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnModifiers\AggregationTypeModifiers;

use Amasty\ReportBuilder\Model\EntityScheme\Column\AggregationType;
use Amasty\ReportBuilder\Model\EntityScheme\Column\DataType;
use Amasty\ReportBuilder\Model\EntityScheme\Provider;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnAggregationTypeResolver;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnModifiers\ColumnModifierInterface;

class ComplexAggregationModifier implements ColumnModifierInterface
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
        $schemeColumn = $this->provider->getEntityScheme()->getColumnById($columnId);
        $dataType = $schemeColumn->getType();
        if (($dataType === DataType::DATE || $dataType === DataType::DATETIME || $dataType === DataType::TIMESTAMP)
            && $schemeColumn->getAggregationType() === AggregationType::TYPE_AVG
        ) {
            $columnData[ColumnAggregationTypeResolver::AGGREGATED_EXPRESSION] =
                'FROM_UNIXTIME(ROUND(AVG(UNIX_TIMESTAMP(%s))))';
        }

        return $columnData;
    }
}
