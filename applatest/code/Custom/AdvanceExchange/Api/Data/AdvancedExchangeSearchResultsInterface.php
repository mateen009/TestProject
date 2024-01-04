<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Custom\AdvanceExchange\Api\Data;

interface AdvancedExchangeSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get advanced_exchange list.
     * @return \Custom\AdvanceExchange\Api\Data\AdvancedExchangeInterface[]
     */
    public function getItems();

    /**
     * Set exchangetype list.
     * @param \Custom\AdvanceExchange\Api\Data\AdvancedExchangeInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

