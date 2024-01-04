<?php

namespace Amasty\RmaAutomation\Model\OptionSource;

use Amasty\Rma\Model\OptionSource\EventInitiator;
use Magento\Framework\Data\OptionSourceInterface;

class UpdatedBy implements OptionSourceInterface
{
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
     * @return array
     */
    public function toArray()
    {
        return [
            EventInitiator::CUSTOMER => __('Customer'),
            EventInitiator::MANAGER => __('Manager'),
            EventInitiator::SYSTEM => __('System')
        ];
    }
}
