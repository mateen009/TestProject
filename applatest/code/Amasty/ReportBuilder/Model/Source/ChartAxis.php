<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Source;

use Amasty\ReportBuilder\Model\EntityScheme\Provider;
use Amasty\ReportBuilder\Model\ReportRegistry;
use Magento\Framework\Exception\LocalizedException;

class ChartAxis implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var ReportRegistry
     */
    private $reportRegistry;

    /**
     * @var Provider
     */
    private $provider;

    public function __construct(
        ReportRegistry $reportRegistry,
        Provider $provider
    ) {
        $this->reportRegistry = $reportRegistry;
        $this->provider = $provider;
    }

    /**
     * @return array
     * @throws LocalizedException
     * @SuppressWarnings("unused")
     */
    public function toOptionArray()
    {
        $options =  [['value' => '', 'label' => __('Please select column')]];
        $report = $this->reportRegistry->getReport();
        $scheme = $this->provider->getEntityScheme();

        foreach ($report->getAllColumns() as $id => $values) {
            try {
                $columnData = $scheme->getColumnById($id);
            } catch (LocalizedException $e) {
                continue;
            }

            $options[] = [
                'value' => $id,
                'label' => $columnData->getTitle() ?: $columnData->getName()
            ];
        }

        return $options;
    }
}
