<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Custom\AdvanceExchange\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface AdvancedExchangeRepositoryInterface
{

    /**
     * Save advanced_exchange
     * @param \Custom\AdvanceExchange\Api\Data\AdvancedExchangeInterface $advancedExchange
     * @return \Custom\AdvanceExchange\Api\Data\AdvancedExchangeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Custom\AdvanceExchange\Api\Data\AdvancedExchangeInterface $advancedExchange
    );

    /**
     * Retrieve advanced_exchange
     * @param string $advancedExchangeId
     * @return \Custom\AdvanceExchange\Api\Data\AdvancedExchangeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($advancedExchangeId);

    /**
     * Retrieve advanced_exchange matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Custom\AdvanceExchange\Api\Data\AdvancedExchangeSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete advanced_exchange
     * @param \Custom\AdvanceExchange\Api\Data\AdvancedExchangeInterface $advancedExchange
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Custom\AdvanceExchange\Api\Data\AdvancedExchangeInterface $advancedExchange
    );

    /**
     * Delete advanced_exchange by ID
     * @param string $advancedExchangeId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($advancedExchangeId);
}

