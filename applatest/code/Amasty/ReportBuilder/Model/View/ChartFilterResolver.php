<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\View;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Provider;

class ChartFilterResolver
{
    /**
     * @var Provider
     */
    private $schemeProvider;

    public function __construct(Provider $schemeProvider)
    {
        $this->schemeProvider = $schemeProvider;
    }

    public function resolveFilters(ReportInterface $report, array $filterData = []): void
    {
        if (!empty($filterData)) {
            $scheme = $this->schemeProvider->getEntityScheme();

            $columns = $report->getAllColumns();
            foreach ($columns as $columnId => &$reportColumnData) {
                $column = $scheme->getColumnById($columnId);
                if (isset($filterData[$column->getAlias()])) {
                    $reportColumnData[ColumnInterface::FILTER] = $filterData[$column->getAlias()];
                }
            }

            $report->setColumns($columns);
        }
    }
}
