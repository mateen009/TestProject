<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Backend\Report;

use Amasty\ReportBuilder\Api\Data\ReportInterface;

class DataCollector
{
    /**
     * @var array
     */
    private $collectors;

    public function __construct(array $collectors = [])
    {
        $this->collectors = $collectors;
    }

    public function execute(ReportInterface $report, array $inputData): void
    {
        foreach ($this->collectors as $collector) {
            if ($collector instanceof DataCollectorInterface) {
                $collectedData = $collector->collect($report, $inputData);
                $report->addData($collectedData);
            }
        }
    }
}
