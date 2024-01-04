<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model;

use Amasty\ReportBuilder\Api\ReportRepositoryInterface;
use Amasty\ReportBuilder\Api\Data\ReportInterface;

class ReportResolver
{
    /**
     * @var ReportRepositoryInterface
     */
    private $repository;

    /**
     * @var ReportRegistry
     */
    private $registry;

    public function __construct(ReportRepositoryInterface $reportRepository, ReportRegistry $registry)
    {
        $this->repository = $reportRepository;
        $this->registry = $registry;
    }

    /**
     * @param int|null $reportId
     * @return ReportInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function resolve(?int $reportId = null): ReportInterface
    {
        $report = $this->registry->getReport();
        if ((!$report->getReportId() && $reportId) || ($reportId && $report->getReportId() != $reportId)) {
            $report = $this->repository->getById($reportId);
            $this->registry->setReport($report);
        }

        return $report;
    }
}
