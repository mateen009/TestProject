<?php
namespace Custom\AdvanceExchange\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class ShipSelect implements ArrayInterface
{
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->getOptions() as $value => $label) {
            $result[] = [
                 'value' => $value,
                 'label' => $label,
             ];
        }

        return $result;
    }

    public function getOptions()
    {
        return [
            'Tyler Technologies - Latham NY' => __('Tyler Technologies - Latham NY')
        ];
    }
}