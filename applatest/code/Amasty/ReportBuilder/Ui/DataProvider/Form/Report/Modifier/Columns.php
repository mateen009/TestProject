<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Ui\DataProvider\Form\Report\Modifier;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Column\AggregationType;
use Amasty\ReportBuilder\Model\EntityScheme\Provider;
use Amasty\ReportBuilder\Model\ReportRegistry;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Amasty\ReportBuilder\Model\EntityScheme\Column\FilterConditionType;

class Columns implements ModifierInterface
{
    const COLUMNS_DATA_KEY = 'chosen_data';
    const COLUMN_DATA_ID = 'id';
    const COLUMN_DATA_ORDER = 'sortStatus';
    const COLUMN_DATA_POSITION = 'position';
    const COLUMN_DATA_FILTER = 'filtration';
    const COLUMN_DATA_AGGREGATION = 'aggregation';
    const COLUMN_DATA_VISIBILITY = 'isVisible';
    const COLUMN_DATA_DATE_FILTER = 'isDate';
    const COLUMN_DATA_AGGREGATION_OPTIONS = 'aggregationOptions';
    const COLUMN_DATA_FILTER_IS_ACTIVE = 'isActive';
    const COLUMN_DATA_FILTER_VALUE = 'value';
    const COLUMN_DATA_CUSTOM_TITLE = 'customTitle';

    /**
     * @var ReportRegistry
     */
    private $reportRegistry;

    /**
     * @var Provider
     */
    private $schemeProvider;

    /**
     * @var AggregationType
     */
    private $aggregationType;

    /**
     * @var Json
     */
    private $serializer;

    public function __construct(
        ReportRegistry $reportRegistry,
        Provider $schemeProvider,
        AggregationType $aggregationType,
        Json $serializer
    ) {
        $this->reportRegistry = $reportRegistry;
        $this->schemeProvider = $schemeProvider;
        $this->aggregationType = $aggregationType;
        $this->serializer = $serializer;
    }

    public function modifyData(array $data)
    {
        $report = $this->reportRegistry->getReport();
        $scheme = $this->schemeProvider->getEntityScheme();
        $columns = [];

        foreach ($report->getAllColumns() as $columnData) {
            $columnId = $columnData[ReportInterface::COLUMN_ID];
            try {
                $schemeColumn = $scheme->getColumnById($columnId);
            } catch (LocalizedException $e) {
                continue;
            }

            $aggregationType = $columnData[ColumnInterface::AGGREGATION_TYPE]
                ?? AggregationType::DEFAULT_AGGREGATION_TYPE;

            $columns[$columnId] = $schemeColumn->toArray();
            $columns[$columnId][ColumnInterface::ID] = $columnId;
            $columns[$columnId][ColumnInterface::ENTITY_NAME] = explode('.', $columnId)[0] ?? '';
            $columns[$columnId][self::COLUMN_DATA_DATE_FILTER] = (bool)$columnData[ColumnInterface::IS_DATE_FILTER];
            $columns[$columnId][self::COLUMN_DATA_ORDER] = (int)$columnData[ColumnInterface::ORDER];
            $columns[$columnId][self::COLUMN_DATA_VISIBILITY] = (bool)$columnData[ColumnInterface::VISIBILITY];
            $columns[$columnId][self::COLUMN_DATA_POSITION] = (int) $columnData[ColumnInterface::POSITION];
            $columns[$columnId][self::COLUMN_DATA_POSITION] = (int) $columnData[ColumnInterface::POSITION];
            $columns[$columnId][self::COLUMN_DATA_AGGREGATION_OPTIONS] = $this->aggregationType->getOptionArray(
                $schemeColumn->getAvailableAggregationTypes()
            );
            $columns[$columnId][self::COLUMN_DATA_CUSTOM_TITLE] = (string) $columnData[ColumnInterface::CUSTOM_TITLE];

            $filterValue = '';
            $hasFilter = isset($columnData[ColumnInterface::FILTER]) && !empty($columnData[ColumnInterface::FILTER]);

            if ($hasFilter) {
                $value = $this->serializer->unserialize($columnData[ColumnInterface::FILTER]);
                $filterValue = isset($value[FilterConditionType::CONDITION_VALUE])
                    ? $value[FilterConditionType::CONDITION_VALUE] : $value;
            }

            $columns[$columnId][self::COLUMN_DATA_FILTER] = [
                self::COLUMN_DATA_FILTER_IS_ACTIVE => $hasFilter,
                self::COLUMN_DATA_FILTER_VALUE => $filterValue
            ];

            $hasAggregation = isset($columnData[ColumnInterface::AGGREGATION_TYPE])
                && !empty($columnData[ColumnInterface::AGGREGATION_TYPE]);
            $aggregationValue = $columnData[ColumnInterface::AGGREGATION_TYPE]
                ?? AggregationType::DEFAULT_AGGREGATION_TYPE;
            $columns[$columnId][self::COLUMN_DATA_AGGREGATION] = [
                self::COLUMN_DATA_FILTER_IS_ACTIVE => $hasAggregation,
                self::COLUMN_DATA_FILTER_VALUE => $aggregationValue
            ];
        }

        $data[$report->getReportId()][self::COLUMNS_DATA_KEY] = array_values($columns);

        return $data;
    }

    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
