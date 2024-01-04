<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnBuilder\Eav\Column;

use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnAggregationTypeResolver;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolverInterface;
use Amasty\ReportBuilder\Model\SelectResolver\RelationBuilder;

class ExpressionResolver
{
    public function resolve(array $columnData, array $relations): array
    {
        $expression = '';

        foreach ($relations as $relation) {
            if ($expression) {
                $expression = sprintf(
                    'IFNULL(%s, %s)',
                    $relation[RelationBuilder::COLUMNS],
                    $expression
                );
            } else {
                $expression = $relation[RelationBuilder::COLUMNS];
            }
        }

        if ($columnData[ColumnAggregationTypeResolver::USE_AGGREGATION]) {
            $expression = sprintf(
                $columnData[ColumnAggregationTypeResolver::AGGREGATED_EXPRESSION],
                $expression
            );
        }

        return [$columnData[ColumnResolverInterface::ALIAS] => $expression];
    }
}
