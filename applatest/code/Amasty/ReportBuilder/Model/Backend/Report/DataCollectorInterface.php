<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Backend\Report;

use Amasty\ReportBuilder\Api\Data\ReportInterface;

interface DataCollectorInterface
{
    public function collect(ReportInterface $report, array $inputData): array;
}
