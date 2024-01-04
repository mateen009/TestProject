<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Backend\Report\DataCollector;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Model\Backend\Report\DataCollector\Columns\FilterCollector;
use Amasty\ReportBuilder\Model\Backend\Report\DataCollectorInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Column\AggregationType;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;

class Columns implements DataCollectorInterface
{
    const COLUMNS_DATA_KEY = 'chosen_data';
    const COLUMN_DATA_ID = 'id';
    const COLUMN_DATA_POSITION = 'position';
    const COLUMN_DATA_ORDER = 'sortStatus';
    const COLUMN_DATA_FILTER = 'filtration';
    const COLUMN_DATA_VISIBILITY = 'isVisible';
    const COLUMN_DATA_DATE_FILTER = 'isDate';
    const COLUMN_DATA_AGGREGATION = 'aggregation';
    const COLUMN_DATA_CUSTOM_TITLE = 'customTitle';
    const ACTIVE_KEY = 'isActive';
    const VALUE_KEY = 'value';

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var FilterCollector
     */
    private $filterCollector;

    public function __construct(Json $serializer, FilterCollector $filterCollector)
    {
        $this->serializer = $serializer;
        $this->filterCollector = $filterCollector;
    }

    public function collect(ReportInterface $report, array $inputData): array
    {
        if (!isset($inputData[self::COLUMNS_DATA_KEY])) {
            return [];
        }

        try {
            $columnsData = $this->serializer->unserialize($inputData[self::COLUMNS_DATA_KEY]);
        } catch (\InvalidArgumentException $e) {
            throw new LocalizedException(__('The problem occurred while parsing column\'s json'), $e);
        }

        return $this->prepareColumns($columnsData);
    }

    private function prepareColumns(array $columnsData): array
    {
        $result[ReportInterface::COLUMNS] = [];
        foreach ($columnsData as $columnData) {
            if (!isset($columnData[self::COLUMN_DATA_ID])) {
                continue;
            }

            $result[ReportInterface::COLUMNS][$columnData[self::COLUMN_DATA_ID]] = [
                ReportInterface::COLUMN_ID => $columnData[self::COLUMN_DATA_ID],
                ColumnInterface::IS_DATE_FILTER => $columnData[self::COLUMN_DATA_DATE_FILTER] ?? 0,
                ColumnInterface::AGGREGATION_TYPE => $this->resolveAggregationType($columnData),
                ColumnInterface::ORDER => $columnData[self::COLUMN_DATA_ORDER] ?? 0,
                ColumnInterface::FILTER => $this->filterCollector->collectFilter($columnData),
                ColumnInterface::VISIBILITY => $columnData[self::COLUMN_DATA_VISIBILITY] ?? 1,
                ColumnInterface::POSITION => $columnData[self::COLUMN_DATA_POSITION] ?? 0,
                ColumnInterface::CUSTOM_TITLE => $columnData[self::COLUMN_DATA_CUSTOM_TITLE] ?? ''
            ];
        }

        return $result;
    }

    private function resolveAggregationType(array $columnData): ?string
    {
        if (isset($columnData[self::COLUMN_DATA_AGGREGATION][self::ACTIVE_KEY])
            && $columnData[self::COLUMN_DATA_AGGREGATION][self::ACTIVE_KEY]
        ) {
            return $columnData[self::COLUMN_DATA_AGGREGATION][self::VALUE_KEY]
                ?? AggregationType::DEFAULT_AGGREGATION_TYPE;
        }

        return null;
    }
}
