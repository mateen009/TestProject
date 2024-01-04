<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\RentalSystem\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * @method getOptionTitle()
 * @method getOptionNumber()
 * @method getPrice()
 */
class RentalOptionType extends AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magenest\RentalSystem\Model\ResourceModel\RentalOptionType::class);
    }
}
