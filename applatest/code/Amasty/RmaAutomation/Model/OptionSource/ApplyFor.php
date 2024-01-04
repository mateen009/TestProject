<?php

namespace Amasty\RmaAutomation\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class ApplyFor
 */
class ApplyFor implements OptionSourceInterface
{
    const NONE = 0;
    const FOR_NEW = 1;
    const FOR_EXISTING = 2;
    const FOR_NEW_AND_EXISTING = 3;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];

        foreach ($this->toArray() as $value => $label) {
            $result[] = ['value' => $value, 'label' => $label];
        }

        return $result;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::NONE => __('None'),
            self::FOR_NEW => __('New Rma'),
            self::FOR_EXISTING => __('Existing Rma'),
            self::FOR_NEW_AND_EXISTING => __('New And Existing Rma')
        ];
    }
}
