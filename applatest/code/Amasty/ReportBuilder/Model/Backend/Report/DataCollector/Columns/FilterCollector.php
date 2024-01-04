<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Backend\Report\DataCollector\Columns;

use Magento\Framework\Serialize\Serializer\Json;

class FilterCollector
{
    const COLUMN_DATA_FILTER = 'filtration';
    const COLUMN_DATA_FILTER_IS_ACTIVE = 'isActive';
    const COLUMN_DATA_FILTER_VALUE = 'value';

    /**
     * @var Json
     */
    private $serializer;

    public function __construct(Json $serializer)
    {
        $this->serializer = $serializer;
    }

    public function collectFilter(array $columnData): string
    {
        $filter = '';
        $isActiveFilter = $columnData[self::COLUMN_DATA_FILTER][self::COLUMN_DATA_FILTER_IS_ACTIVE] ?? false;
        if ($isActiveFilter) {
            $value = $columnData[self::COLUMN_DATA_FILTER][self::COLUMN_DATA_FILTER_VALUE];
            if (!is_array($value)) {
                $value = ['value' => $value];
            }
            $filter = $value ? $this->serializer->serialize($value) : $value;
        }

        return $filter;
    }
}
