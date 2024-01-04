<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Custom\AdvanceExchange\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class McgSku extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('eligible_sku', 'id');
    }
}