<?php
namespace Custom\AdvanceExchange\Model\Source;

use Magento\Framework\Option\ArrayInterface;
 
class ExchangeType implements ArrayInterface
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
            'Ship on Request' => __('Ship on Request'),
            'Ship on Return' => __('Ship on Return'),
            'Repair and Return' => __('Repair and Return'),
            'Lost or Stolen' => __('Lost or Stolen')
        ];
    }
}