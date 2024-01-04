<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model;

use Amasty\ReportBuilder\Api\Data\ReportInterface;

class ReportRegistry
{
    /**
     * @var ReportInterface
     */
    private $report;

    /**
     * @var ReportFactory
     */
    private $reportFactory;

    public function __construct(ReportFactory $reportFactory)
    {
        $this->reportFactory = $reportFactory;
    }

    public function setReport(ReportInterface $report): void
    {
        $this->report = $report;
    }

    public function getReport(): ReportInterface
    {
        if (!$this->report) {
            $this->report = $this->reportFactory->create();
        }

        return $this->report;
    }
}
