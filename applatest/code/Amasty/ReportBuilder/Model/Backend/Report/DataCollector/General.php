<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Backend\Report\DataCollector;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Model\Backend\Report\DataCollectorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Store;

class General implements DataCollectorInterface
{
    public function collect(ReportInterface $report, array $inputData): array
    {
        $data = [];

        if (!isset($inputData[ReportInterface::MAIN_ENTITY]) || empty($inputData[ReportInterface::MAIN_ENTITY])) {
            throw new LocalizedException(__('Main entity is a required field for Report'));
        }

        $data[ReportInterface::REPORT_ID] = $inputData[ReportInterface::REPORT_ID] ?? null;
        $data[ReportInterface::REPORT_ID] = $inputData[ReportInterface::REPORT_ID] ?: null;
        $data[ReportInterface::STORE_IDS] = $inputData[ReportInterface::STORE_IDS] ?? [Store::DEFAULT_STORE_ID];
        $data[ReportInterface::NAME] = $inputData[ReportInterface::NAME] ?? '';
        $data[ReportInterface::MAIN_ENTITY] = $inputData[ReportInterface::MAIN_ENTITY];
        $usePeriod = $inputData[ReportInterface::USE_PERIOD] ?? false;
        $data[ReportInterface::USE_PERIOD] = filter_var($usePeriod, FILTER_VALIDATE_BOOLEAN);
        $displayChart = $inputData[ReportInterface::DISPLAY_CHART] ?? false;
        $data[ReportInterface::DISPLAY_CHART] = filter_var($displayChart, FILTER_VALIDATE_BOOLEAN);
        $data[ReportInterface::CHART_AXIS_X] = $inputData[ReportInterface::CHART_AXIS_X] ?? '';
        $data[ReportInterface::CHART_AXIS_Y] = $inputData[ReportInterface::CHART_AXIS_Y] ?? '';

        return $data;
    }
}
