<?php
/**
 * Copyright © 2020 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Plugin;

use Magento\InventoryConfiguration\Model\IsSourceItemManagementAllowedForProductType;

/**
 * Class DisableSourceManagement
 * @package Magenest\RentalSystem\Plugin
 */
class DisableSourceManagement
{
    public function afterExecute(IsSourceItemManagementAllowedForProductType $subject, bool $result, string $productType)
    {
        if ($productType == 'rental') {
            return false;
        }

        return $result;
    }
}