<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Api\ReportRepositoryInterface;
use Amasty\ReportBuilder\Model\ReportFactory;
use Amasty\ReportBuilder\Model\ResourceModel\Report as ReportResource;
use Amasty\ReportBuilder\Model\ResourceModel\Report\CollectionFactory;
use Amasty\ReportBuilder\Model\ResourceModel\Report\Collection;
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
class ReportRepository implements ReportRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var ReportFactory
     */
    private $reportFactory;

    /**
     * @var ReportResource
     */
    private $reportResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $reports;

    /**
     * @var CollectionFactory
     */
    private $reportCollectionFactory;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        ReportFactory $reportFactory,
        ReportResource $reportResource,
        CollectionFactory $reportCollectionFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->reportFactory = $reportFactory;
        $this->reportResource = $reportResource;
        $this->reportCollectionFactory = $reportCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(ReportInterface $report): ReportInterface
    {
        try {
            if ($report->getReportId()) {
                $report = $this->getById((int) $report->getReportId())->addData($report->getData());
            }
            $this->reportResource->save($report);
            unset($this->reports[$report->getReportId()]);
        } catch (\Exception $e) {
            if ($report->getReportId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save report with ID %1. Error: %2',
                        [$report->getReportId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new report. Error: %1', $e->getMessage()));
        }

        return $report;
    }

    /**
     * @inheritdoc
     */
    public function getNew(): ReportInterface
    {
        return $this->reportFactory->create();
    }

    /**
     * @inheritdoc
     */
    public function getById(int $id): ReportInterface
    {
        if (!isset($this->reports[$id])) {
            /** @var \Amasty\ReportBuilder\Model\Report $report */
            $report = $this->reportFactory->create();
            $this->reportResource->load($report, $id);
            if (!$report->getReportId()) {
                throw new NoSuchEntityException(__('Report with specified ID "%1" not found.', $id));
            }
            $this->reports[$id] = $report;
        }

        return $this->reports[$id];
    }

    /**
     * @inheritdoc
     */
    public function delete(ReportInterface $report): bool
    {
        try {
            $this->reportResource->delete($report);
            unset($this->reports[$report->getReportId()]);
        } catch (\Exception $e) {
            if ($report->getReportId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove report with ID %1. Error: %2',
                        [$report->getReportId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove report. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById(int $id): bool
    {
        $reportModel = $this->getById($id);
        $this->delete($reportModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\ReportBuilder\Model\ResourceModel\Report\Collection $reportCollection */
        $reportCollection = $this->reportCollectionFactory->create();

        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $reportCollection);
        }

        $searchResults->setTotalCount($reportCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();

        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $reportCollection);
        }

        $reportCollection->setCurPage($searchCriteria->getCurrentPage());
        $reportCollection->setPageSize($searchCriteria->getPageSize());

        $reports = [];
        /** @var ReportInterface $report */
        foreach ($reportCollection->getItems() as $report) {
            $reports[] = $this->getById((int) $report->getReportId());
        }

        $searchResults->setItems($reports);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $reportCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $reportCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $reportCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection $reportCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $reportCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $reportCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }

    /**
     * @param ReportInterface $report
     * @return ReportInterface
     */
    public function duplicate(ReportInterface $report): ReportInterface
    {
        $newReport = clone $report;
        $newReport->setReportId(null);
        $newReport->setName(__('Copy of %1', $report->getName())->render());
        $this->save($newReport);

        return $newReport;
    }
}
