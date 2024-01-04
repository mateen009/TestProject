<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver;

class ColumnAggregationTypeResolver
{
    const AGGREGATED_EXPRESSION = 'aggregated_expression';
    const USE_AGGREGATION = 'use_aggregation';

    /**
     * @var array
     */
    private $pool;

    public function __construct(array $pool = [])
    {
        $this->pool = $pool;
    }

    /**
     * Modify column data used for ColumnBuilders.
     *
     * @param string $columnId
     * @param array $columnData
     * @return array
     */
    public function build(string $columnId, array $columnData): array
    {
        foreach ($this->pool as $modifier) {
            $columnData = $modifier->modify($columnId, $columnData);
        }

        return $columnData;
    }
}
