<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Model\Config\Source;

class DateFormat implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('MM/DD'), 'value' => 'MM/dd'],
            ['label' => __('DD/MM'), 'value' => 'dd/MM'],
            ['label' => __('MM/DD/YYYY'), 'value' => 'MM/dd/YYYY'],
            ['label' => __('DD/MM/YYYY'), 'value' => 'dd/MM/YYYY'],
            ['label' => __('MM/DD/YY'), 'value' => 'MM/dd/YY'],
            ['label' => __('DD/MM/YY'), 'value' => 'dd/MM/YY'],
        ];
    }
}
