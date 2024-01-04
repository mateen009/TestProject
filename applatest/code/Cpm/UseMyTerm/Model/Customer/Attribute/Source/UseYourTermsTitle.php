<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Cpm\UseMyTerm\Model\Customer\Attribute\Source;

class UseYourTermsTitle extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['value' => '1', 'label' => __('Use My Authorized Payment Terms - Net 15')],
                ['value' => '2', 'label' => __('Use My Authorized Payment Terms - Net 20')],
                ['value' => '3', 'label' => __('Use My Authorized Payment Terms - Net 30')],
                ['value' => '4', 'label' => __('Use My Authorized Payment Terms - 1% 10 Net 30')],
                ['value' => '5', 'label' => __('Use My Authorized Payment Terms - 2% 10 Net 30')],
                ['value' => '6', 'label' => __('Use My Authorized Payment Terms - Net 45')],
                ['value' => '7', 'label' => __('Use My Authorized Payment Terms - 2% 30 Net 60')],
                ['value' => '8', 'label' => __('Use My Authorized Payment Terms - 2% Net 30')],
                ['value' => '9', 'label' => __('Use My Authorized Payment Terms - 3% 10 Net 60')],
                ['value' => '10', 'label' => __('Use My Authorized Payment Terms - Net 10')],
                ['value' => '11', 'label' => __('Use My Authorized Payment Terms - Net 60')],
                ['value' => '12', 'label' => __('Use My Authorized Payment Terms - Net Due 15th Following Month')],
                ['value' => '13', 'label' => __('Use My Authorized Payment Terms - Due on Receipt')]
            ];
        }
        return $this->_options;
    }
}

