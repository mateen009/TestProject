<?php
namespace Custom\AdvanceExchange\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class ShipLabelSelect implements ArrayInterface
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
            'Add New Address' => __('Add New Address')
        ];
    }
}