<?php

namespace AscentDigital\NetsuiteConnector\Model\Customer\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class AllowedDepotServiceType extends AbstractSource
{
    public function getAllOptions()
    {
        return [
            'option1' => [
                'label' => 'Ship on Request',
                'value' => 'Ship on Request'
            ],
            'option2' => [
                'label' => 'Ship on Return',
                'value' => 'Ship on Return'
            ],
            'option3' => [
                'label' => 'Repair and Return',
                'value' => 'Repair and Return'
            ],
            'option4' => [
                'label' => 'Lost or Stolen',
                'value' => 'Lost or Stolen'
            ]
        ];
    }
}