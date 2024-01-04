<?php
/**
 * Copyright © 2020 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Plugin;

use Magento\Wishlist\Helper\Data;

/**
 * Class WishlistUrl
 * @package Magenest\RentalSystem\Plugin
 */
class WishlistUrl
{
    /**
     * @param Data $subject
     * @param callable $proceed
     * @param \Magento\Catalog\Model\Product|\Magento\Wishlist\Model\Item $item
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundGetConfigureUrl(Data $subject, callable $proceed, $item)
    {
        $productType = $item->getProduct()->getTypeId();
        if ($productType == 'rental' || $productType == 'ticket') {
            return $item->getProductUrl();
        }

        return $proceed($item);
    }
}
