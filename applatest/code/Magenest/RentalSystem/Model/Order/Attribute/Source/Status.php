<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Model\Order\Attribute\Source;

use Magento\Catalog\Model\Product\Attribute\Source\Status as CoreStatus;
use Magenest\RentalSystem\Model\Status as SourceStatus;

class Status extends CoreStatus
{
    public static function getOptionArray()
    {
        return [
            SourceStatus::UNPAID     => __('Unpaid'),
            SourceStatus::PENDING    => __('Pending'),
            SourceStatus::DELIVERING => __('Delivering'),
            SourceStatus::DELIVERED  => __('Delivered'),
            SourceStatus::RETURNING  => __('Returning'),
            SourceStatus::COMPLETE   => __('Complete')
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
}
