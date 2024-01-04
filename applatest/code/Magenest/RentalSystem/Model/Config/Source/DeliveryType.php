<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Data\OptionSourceInterface;

class DeliveryType implements SourceInterface, OptionSourceInterface
{
    const SHIPPING = 0;
    const PICKUP   = 1;
    const BOTH     = 2;

    /**
     * Retrieve option array
     * @return string[]
     */
    public static function getOptionArray()
    {
        return [
            self::SHIPPING => __('Shipping'),
            self::PICKUP   => __('Local pickup'),
            self::BOTH     => __('Both')
        ];
    }

    /**
     * Retrieve option array with empty value
     * @return string[]
     */
    public function getAllOptions()
    {
        $result = [];

        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }

    /**
     * Retrieve option text by option value
     *
     * @param string $optionId
     *
     * @return string
     */
    public function getOptionText($optionId)
    {
        $options = self::getOptionArray();

        return $options[$optionId] ?? null;
    }

    /**
     * Get options as array
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}
