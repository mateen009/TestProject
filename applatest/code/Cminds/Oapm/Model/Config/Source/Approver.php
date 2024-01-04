<?php
namespace Cminds\Oapm\Model\Config\Source;

class Approver implements \Magento\Framework\Option\ArrayInterface
{
    const APPROVER_ADMIN = 1;
    const APPROVER_CUSTOMER = 0;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::APPROVER_CUSTOMER,
                'label' => __('Customer')
            ],
            [
                'value' => self::APPROVER_ADMIN,
                'label' => __('Admin')
            ]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(array $arrAttributes = [])
    {
        return [
            self::APPROVER_CUSTOMER => __('Customer'),
            self::APPROVER_ADMIN => __('Admin'),
        ];
    }
}
