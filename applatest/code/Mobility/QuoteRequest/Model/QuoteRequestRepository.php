<?php

namespace Mobility\QuoteRequest\Model;

use Mobility\QuoteRequest\Api\Data\QuoteRequestInterface;
use Mobility\QuoteRequest\Api\QuoteRequestRepositoryInterface;
use Mobility\QuoteRequest\Model\QuoteRequestFactory;
use Mobility\QuoteRequest\Model\ResourceModel\QuoteRequest as QuoteRequestResource;
use Mobility\QuoteRequest\Model\ResourceModel\QuoteRequest\CollectionFactory;
use Mobility\QuoteRequest\Model\ResourceModel\QuoteRequest\Collection;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteRequestRepository implements QuoteRequestRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var QuoteRequestFactory
     */
    private $quoteRequestFactory;

    /**
     * @var QuoteRequestResource
     */
    private $quoteRequestResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $quoteRequests;

    /**
     * @var CollectionFactory
     */
    private $quoteRequestCollectionFactory;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        QuoteRequestFactory $quoteRequestFactory,
        QuoteRequestResource $quoteRequestResource,
        CollectionFactory $quoteRequestCollectionFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->quoteRequestFactory = $quoteRequestFactory;
        $this->quoteRequestResource = $quoteRequestResource;
        $this->quoteRequestCollectionFactory = $quoteRequestCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(QuoteRequestInterface $quoteRequest)
    {
        try {
            $this->quoteRequestResource->save($quoteRequest);
            return $this;
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('Could not save the entry: %1', $e->getMessage())
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function getNew(): QuoteRequestInterface
    {
        return $this->quoteRequestFactory->create();
    }

    /**
     * @inheritdoc
     */
    public function getCustomerQuoteRequestList($customerId, $requestStatus = [], $quoteId = null, $orderFrom = null, $orderTo = null, $orderSearch = null, $status = null)
    {

        $requestQuoteCollection = $this->quoteRequestFactory->create()
            ->getCollection();


        $requestQuoteCollection->addFieldToFilter('customer_id', $customerId)->setOrder('created_at', 'desc');


        if ($quoteId) {
            $requestQuoteCollection->addFieldToFilter('quote_id', $quoteId)->setOrder('created_at', 'desc');
        }
        // if (count($requestStatus)) {
        //     $requestQuoteCollection->addFieldToFilter('status', $requestStatus)->setOrder('created_at', 'desc');
        // }

        if (isset($orderSearch)) {
            $requestQuoteCollection->addFieldToFilter('created_at', array('from' => $orderFrom, 'to' => $orderTo))->setOrder('created_at', 'desc');
        }

        if (isset($status)) {
            if ($status == 'converted') {
                $requestQuoteCollection->addFieldToFilter('order_id', ['neq' => ''])->setOrder('created_at', 'desc');
            } elseif ($status == 'notconverted') {
                $requestQuoteCollection->addFieldToFilter('order_id', ['eq' => ''])->setOrder('created_at', 'desc');
            }
        }

        return $requestQuoteCollection;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerQuoteApproval1List($customerId, $requestStatus = [])
    {
        $requestQuoteCollection = $this->quoteRequestFactory->create()
            ->getCollection();
        $requestQuoteCollection->addFieldToFilter('approval_1_id', $customerId)->setOrder('anticipated_demo_start_date', 'desc');

        if (count($requestStatus)) {
            $requestQuoteCollection->addFieldToFilter('status', $requestStatus)->setOrder('anticipated_demo_start_date', 'desc');
        }

        return $requestQuoteCollection;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerQuoteApproval2List($customerId, $requestStatus = [])
    {
        $requestQuoteCollection = $this->quoteRequestFactory->create()
            ->getCollection();
        $requestQuoteCollection->addFieldToFilter('approval_2_id', $customerId)->setOrder('anticipated_demo_start_date', 'desc');

        if (count($requestStatus)) {
            $requestQuoteCollection->addFieldToFilter('status', $requestStatus)->setOrder('anticipated_demo_start_date', 'desc');
        }

        return $requestQuoteCollection;
    }

    /**
     * @inheritdoc
     */
    public function getById($id)
    {
        try {
            /** @var \Mobility\QuoteRequest\Model\QuoteRequest $quoteRequest */
            $quoteRequest = $this->quoteRequestFactory->create();
            $this->quoteRequestResource->load($quoteRequest, $id);
            if (!$quoteRequest->getId()) {
                throw new NoSuchEntityException(__('Quote Request with specified ID "%1" not found.', $id));
            }
            return $quoteRequest;
        } catch (\Exception $e) {
            throw new NoSuchEntityException(
                __('Could not find the entry: %1', $e->getMessage())
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function delete(QuoteRequestInterface $quoteRequest)
    {
        try {
            $this->quoteRequestResource->delete($quoteRequest);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(
                __('Unable to remove quote request. Error: %1', $e->getMessage())
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function deleteById($id)
    {
        try {
            return $this->quoteRequestFactory->create()
                ->getCollection()
                ->addFieldToFilter('id', $id)
                ->walk('delete');
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(
                __('Unable to remove quote request. Error: %1', $e->getMessage())
            );
        }
    }
}
