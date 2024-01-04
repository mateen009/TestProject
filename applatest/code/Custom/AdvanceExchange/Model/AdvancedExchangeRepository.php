<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Custom\AdvanceExchange\Model;

use Custom\AdvanceExchange\Api\AdvancedExchangeRepositoryInterface;
use Custom\AdvanceExchange\Api\Data\AdvancedExchangeInterface;
use Custom\AdvanceExchange\Api\Data\AdvancedExchangeInterfaceFactory;
use Custom\AdvanceExchange\Api\Data\AdvancedExchangeSearchResultsInterfaceFactory;
use Custom\AdvanceExchange\Model\ResourceModel\AdvancedExchange as ResourceAdvancedExchange;
use Custom\AdvanceExchange\Model\ResourceModel\AdvancedExchange\CollectionFactory as AdvancedExchangeCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class AdvancedExchangeRepository implements AdvancedExchangeRepositoryInterface
{

    /**
     * @var AdvancedExchangeInterfaceFactory
     */
    protected $advancedExchangeFactory;

    /**
     * @var AdvancedExchange
     */
    protected $searchResultsFactory;

    /**
     * @var AdvancedExchangeCollectionFactory
     */
    protected $advancedExchangeCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var ResourceAdvancedExchange
     */
    protected $resource;


    /**
     * @param ResourceAdvancedExchange $resource
     * @param AdvancedExchangeInterfaceFactory $advancedExchangeFactory
     * @param AdvancedExchangeCollectionFactory $advancedExchangeCollectionFactory
     * @param AdvancedExchangeSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceAdvancedExchange $resource,
        AdvancedExchangeInterfaceFactory $advancedExchangeFactory,
        AdvancedExchangeCollectionFactory $advancedExchangeCollectionFactory,
        AdvancedExchangeSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->advancedExchangeFactory = $advancedExchangeFactory;
        $this->advancedExchangeCollectionFactory = $advancedExchangeCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(
        AdvancedExchangeInterface $advancedExchange
    ) {
        try {
            $this->resource->save($advancedExchange);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the advancedExchange: %1',
                $exception->getMessage()
            ));
        }
        return $advancedExchange;
    }

    /**
     * @inheritDoc
     */
    public function get($advancedExchangeId)
    {
        $advancedExchange = $this->advancedExchangeFactory->create();
        $this->resource->load($advancedExchange, $advancedExchangeId);
        if (!$advancedExchange->getId()) {
            throw new NoSuchEntityException(__('Data with id "%1" does not exist.', $advancedExchangeId));
        }
        return $advancedExchange;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->advancedExchangeCollectionFactory->create();
        
        $this->collectionProcessor->process($criteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        
        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }
        
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(
        AdvancedExchangeInterface $advancedExchange
    ) {
        try {
            $advancedExchangeModel = $this->advancedExchangeFactory->create();
            $this->resource->load($advancedExchangeModel, $advancedExchange->getAdvancedExchangeId());
            $this->resource->delete($advancedExchangeModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the record: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($advancedExchangeId)
    {
        return $this->delete($this->get($advancedExchangeId));
    }
}

