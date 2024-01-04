<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace AscentDigital\NetsuiteConnector\Model\Customer\Attribute\Source;

class CustomerCarrier extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * getAllOptions
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['value' => '', 'label' => __(' ')],
                ['value' => '1', 'label' => __('UPS CARRIER')],
                ['value' => '2', 'label' => __('USPS CARRIER')],
                ['value' => '3', 'label' => __('FEDEX CARRIER')]
                ];
        }
        return $this->_options;
    }
}

