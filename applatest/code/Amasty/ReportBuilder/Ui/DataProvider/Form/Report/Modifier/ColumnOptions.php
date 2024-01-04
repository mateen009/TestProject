<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Ui\DataProvider\Form\Report\Modifier;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Provider;
use Amasty\ReportBuilder\Model\ReportRegistry;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Column\OptionsResolver;

class ColumnOptions implements ModifierInterface
{
    const COLUMN_DATA_OPTIONS = 'options';

    /**
     * @var ReportRegistry
     */
    private $reportRegistry;

    /**
     * @var Provider
     */
    private $schemeProvider;

    /**
     * @var OptionsResolver
     */
    private $optionsResolver;

    public function __construct(
        ReportRegistry $reportRegistry,
        Provider $schemeProvider,
        OptionsResolver $optionsResolver
    ) {
        $this->reportRegistry = $reportRegistry;
        $this->schemeProvider = $schemeProvider;
        $this->optionsResolver = $optionsResolver;
    }

    public function modifyData(array $data)
    {
        $report = $this->reportRegistry->getReport();
        $scheme = $this->schemeProvider->getEntityScheme();

        if (!isset($data[$report->getReportId()][Columns::COLUMNS_DATA_KEY])) {
            return $data;
        }

        foreach ($data[$report->getReportId()][Columns::COLUMNS_DATA_KEY] as &$column) {
            $schemeColumn = $scheme->getColumnById($column[ColumnInterface::ID]);
            $column[self::COLUMN_DATA_OPTIONS] = $this->optionsResolver->resolve($schemeColumn);
        }

        return $data;
    }

    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
