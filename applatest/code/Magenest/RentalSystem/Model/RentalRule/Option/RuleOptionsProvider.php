<?php
namespace Magenest\RentalSystem\Model\RentalRule\Option;

use Magento\Framework\Data\OptionSourceInterface;

class RuleOptionsProvider implements OptionSourceInterface
{
    const OPTION_TYPE_NOT_USE_RECURRING = 0;
    const OPTION_TYPE_EVERY_DAY = 1;
    const OPTION_TYPE_EVERY_WEEK = 2;
    const OPTION_TYPE_EVERY_MONTH = 3;
    const OPTION_TYPE_EVERY_YEAR = 4;

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::OPTION_TYPE_NOT_USE_RECURRING,
                'label' => __('Not Use Recurring')
            ],
            [
                'value' => self::OPTION_TYPE_EVERY_DAY,
                'label' => __('Every Day')
            ],
            [
                'value' => self::OPTION_TYPE_EVERY_WEEK,
                'label' => __('Every Week')
            ],
            [
                'value' => self::OPTION_TYPE_EVERY_MONTH,
                'label' => __('Every Month')
            ],
            [
                'value' => self::OPTION_TYPE_EVERY_YEAR,
                'label' => __('Every Year')
            ]
        ];
    }
}
