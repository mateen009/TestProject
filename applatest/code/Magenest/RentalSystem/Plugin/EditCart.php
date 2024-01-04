<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Plugin;

use Magento\Catalog\Model\Product as ModelProduct;
use Magento\Framework\DataObject;
use Magento\Catalog\Helper\Product;

/**
 * Class EditCart
 * @package Magenest\RentalSystem\Plugin
 */
class EditCart
{
    /**
     * @param Product $subject
     * @param ModelProduct $product
     * @param DataObject $buyRequest
     */
    public function beforePrepareProductOptions(Product $subject, ModelProduct $product, DataObject $buyRequest)
    {
        if ($product->getTypeId() == 'rental')
            $buyRequest->setData('options', $buyRequest->getData('additional_options'));
    }
}