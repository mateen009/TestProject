<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Api;

interface ReportRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\ReportBuilder\Api\Data\ReportInterface $report
     *
     * @return \Amasty\ReportBuilder\Api\Data\ReportInterface
     */
    public function save(
        \Amasty\ReportBuilder\Api\Data\ReportInterface $report
    ): \Amasty\ReportBuilder\Api\Data\ReportInterface;

    /**
     * @return \Amasty\ReportBuilder\Api\Data\ReportInterface
     */
    public function getNew(): \Amasty\ReportBuilder\Api\Data\ReportInterface;

    /**
     * Get by id
     *
     * @param int $id
     *
     * @return \Amasty\ReportBuilder\Api\Data\ReportInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $id): \Amasty\ReportBuilder\Api\Data\ReportInterface;

    /**
     * Delete
     *
     * @param \Amasty\ReportBuilder\Api\Data\ReportInterface $report
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\ReportBuilder\Api\Data\ReportInterface $report): bool;

    /**
     * Delete by id
     *
     * @param int $id
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById(int $id): bool;

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ): \Magento\Framework\Api\SearchResultsInterface;

    /**
     * @param \Amasty\ReportBuilder\Api\Data\ReportInterface $report
     * @return \Amasty\ReportBuilder\Api\Data\ReportInterface Duplicated report
     */
    public function duplicate(
        \Amasty\ReportBuilder\Api\Data\ReportInterface $report
    ): \Amasty\ReportBuilder\Api\Data\ReportInterface;
}
