<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Custom\AdvanceExchange\Model\ResourceModel\AdvancedExchange;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'advanced_exchange_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Custom\AdvanceExchange\Model\AdvancedExchange::class,
            \Custom\AdvanceExchange\Model\ResourceModel\AdvancedExchange::class
        );
    }
}

