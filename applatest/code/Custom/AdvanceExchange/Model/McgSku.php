<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */


namespace Custom\AdvanceExchange\Model;

class McgSku extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Custom\AdvanceExchange\Model\ResourceModel\McgSku');
    }
}