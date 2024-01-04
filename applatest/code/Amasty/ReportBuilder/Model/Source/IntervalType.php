<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Source;

class IntervalType implements \Magento\Framework\Data\OptionSourceInterface
{
    const TYPE_DAY = 'day';
    const TYPE_WEEK = 'week';
    const TYPE_MONTH = 'month';
    const TYPE_YEAR = 'year';

    public function toOptionArray()
    {
        return [
            [
                'value' => self::TYPE_DAY,
                'label' => __('Day')
            ],
            [
                'value' => self::TYPE_WEEK,
                'label' => __('Week')
            ],
            [
                'value' => self::TYPE_MONTH,
                'label' => __('Month')
            ],
            [
                'value' => self::TYPE_YEAR,
                'label' => __('Year')
            ],
        ];
    }
}
