<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Model\Product\Type;

use Magento\Catalog\Model\Product\Type\AbstractType;

class Rental extends AbstractType
{
    /**
     * Check if shipping is applied
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function isVirtual($product)
    {
        if ($product->hasCustomOptions()) {
            $options = $product->getCustomOptions();
            if (!empty($options['info_buyRequest'])) {
                $rentalData = $this->serializer->unserialize($options['info_buyRequest']->getValue());
                if (isset($rentalData['additional_options']['local_pickup'])) {
                    return $rentalData['additional_options']['local_pickup'] == 1;
                }
            }
        }

        return false;
    }

    /**
     * Delete data specific for Rental product type
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    public function deleteTypeSpecificData(\Magento\Catalog\Model\Product $product)
    {
    }

    /**
     * Check if product has options
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return boolean
     */
    public function hasOptions($product)
    {
        return true;
    }
}
