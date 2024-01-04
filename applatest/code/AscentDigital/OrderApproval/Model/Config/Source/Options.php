<?php
namespace AscentDigital\OrderApproval\Model\Config\Source;
class Options extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
    * Get all options
    *
    * @return array
    */
    public function getAllOptions()
    {
        $this->_options = [
                ['label' => __('No'), 'value'=>'0'],
                ['label' => __('Yes'), 'value'=>'1'],
                ['label' => __('Partial'), 'value'=>'2']
            ];
    return $this->_options;
    }
}