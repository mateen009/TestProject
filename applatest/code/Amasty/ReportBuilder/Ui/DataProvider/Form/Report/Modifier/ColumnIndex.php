<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Ui\DataProvider\Form\Report\Modifier;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Provider;
use Amasty\ReportBuilder\Model\ReportRegistry;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class ColumnIndex implements ModifierInterface
{
    const COLUMN_DATA_INDEX = 'index';

    /**
     * @var ReportRegistry
     */
    private $reportRegistry;

    /**
     * @var Provider
     */
    private $schemeProvider;

    public function __construct(
        ReportRegistry $reportRegistry,
        Provider $schemeProvider
    ) {
        $this->reportRegistry = $reportRegistry;
        $this->schemeProvider = $schemeProvider;
    }

    public function modifyData(array $data)
    {
        $report = $this->reportRegistry->getReport();
        $scheme = $this->schemeProvider->getEntityScheme();

        if (!isset($data[$report->getReportId()][Columns::COLUMNS_DATA_KEY])) {
            return $data;
        }

        foreach ($data[$report->getReportId()][Columns::COLUMNS_DATA_KEY] as &$column) {
            $column[self::COLUMN_DATA_INDEX] = $scheme->getEntityByName($column[ColumnInterface::ENTITY_NAME])
                ->getColumnIndex($column[ColumnInterface::NAME]);
        }

        return $data;
    }

    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
