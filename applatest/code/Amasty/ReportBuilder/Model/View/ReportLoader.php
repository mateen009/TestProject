<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\View;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Api\ReportRepositoryInterface;
use Amasty\ReportBuilder\Model\ReportRegistry;
use Magento\Framework\App\RequestInterface;

class ReportLoader
{
    /**
     * @var ReportRepositoryInterface
     */
    private $reportRepository;

    /**
     * @var ReportRegistry
     */
    private $registry;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        RequestInterface $request,
        ReportRepositoryInterface $reportRepository,
        ReportRegistry $registry
    ) {
        $this->reportRepository = $reportRepository;
        $this->registry = $registry;
        $this->request = $request;
    }

    public function execute(): ReportInterface
    {
        $reportId = (int)$this->request->getParam(ReportInterface::REPORT_ID);
        if ($reportId) {
            /** @var ReportInterface $report **/
            $report = $this->reportRepository->getById($reportId);
        } else {
            $report = $this->reportRepository->getNew();
        }

        $this->registry->setReport($report);

        return $report;
    }
}
