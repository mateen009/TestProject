<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Model\Config\Source;

class TimeFormat implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('12 hours'), 'value' => ' hh:mm a'],
            ['label' => __('24 hours'), 'value' => ' HH:mm'],
        ];
    }
}
