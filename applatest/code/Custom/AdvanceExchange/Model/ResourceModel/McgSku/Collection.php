<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */


namespace Custom\AdvanceExchange\Model\ResourceModel\McgSku;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Custom\AdvanceExchange\Model\McgSku', 'Custom\AdvanceExchange\Model\ResourceModel\McgSku');
    }
}