<?php
/**
 * Copyright © 2020 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Helper;

use Magento\Multishipping\Helper\Data;

class Multishipping extends Data
{
    /**
     * @return bool
     */
    public function isMultishippingCheckoutAvailable()
    {
        $quote = $this->getQuote();
        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->getProductType() == 'rental') {
                //TODO: solve multi shipping address issue with custom product price
                return false;
            }
        }

        return parent::isMultishippingCheckoutAvailable();
    }
}
