<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\RentalSystem\Model;

use Magento\Framework\Model\AbstractModel;

class RentalOrder extends AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magenest\RentalSystem\Model\ResourceModel\RentalOrder::class);
    }

    /**
     * @param $id
     *
     * @return RentalOrder
     */
    public function loadByOrderItemId($id)
    {
        return $this->load($id, 'order_item_id');
    }
}
