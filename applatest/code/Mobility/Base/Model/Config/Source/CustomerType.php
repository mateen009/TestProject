<?php
namespace Mobility\Base\Model\Config\Source;

class CustomerType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    const CUSTOMER_TYPE_OPTIONS = [
        1 => 'Sales Rep',
        2 => 'End Customer (Ship-To)',
        3 => 'Sales Manager',
        4 => 'Territory Manager',
        5 => 'Executive Manager'
    ];
    /**
     * Get all options
     * 
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options[] = ['value' => '', 'label' => __('Please select Customer Type')];
            foreach (self::CUSTOMER_TYPE_OPTIONS as $key => $value) {
                $this->_options[] = ['label' => $value, 'value'=> $key];
            }
        }

        return $this->_options;
    }
}
