<?php
namespace Magenest\RentalSystem\Model\RentalRule\Option;

use Magento\Framework\Data\OptionSourceInterface;

class RepeatOptions implements OptionSourceInterface
{
    const OPTION_BY_HOURS = 0;
    const OPTION_BY_DAYS = 1;
    const OPTION_BY_MONTHS = 2;
    const MAPPING = [
        self::OPTION_BY_HOURS => "hour(s)",
        self::OPTION_BY_DAYS => "day(s)",
        self::OPTION_BY_MONTHS => "month(s)"
    ];

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::OPTION_BY_HOURS,
                'label' => __('By Hours')
            ],
            [
                'value' => self::OPTION_BY_DAYS,
                'label' => __('By Days')
            ],
            [
                'value' => self::OPTION_BY_MONTHS,
                'label' => __('By Months')
            ]
        ];
    }
}
