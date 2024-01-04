<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Ui\DataProvider\Form\Report\Modifier;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Model\ReportRegistry;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class BooleanData implements ModifierInterface
{
    /**
     * @var ReportRegistry
     */
    private $reportRegistry;

    public function __construct(
        ReportRegistry $reportRegistry
    ) {
        $this->reportRegistry = $reportRegistry;
    }

    public function modifyData(array $data)
    {
        $report = $this->reportRegistry->getReport();
        if (isset($data[$report->getReportId()])) {
            $data[$report->getReportId()][ReportInterface::USE_PERIOD] = $report->getUsePeriod();
            $data[$report->getReportId()][ReportInterface::DISPLAY_CHART] = $report->getDisplayChart();
        }

        return $data;
    }

    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
