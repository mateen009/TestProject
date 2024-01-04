<?php
namespace AscentDigital\NetsuiteConnector\Model\Config;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class BrandOptions extends AbstractSource
{
    public function getAllOptions()
    {
        // Replace this with your brand options
        return [
            ['label' => ' ', 'value' => ''],
            ['label' => 'Samsung', 'value' => '1'],
            ['label' => 'Apple', 'value' => '2'],
            ['label' => 'Oppo', 'value' => '3'],
            // Add more options as needed
        ];
    }
}