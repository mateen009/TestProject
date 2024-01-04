<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Ui\Component\Listing\View;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Provider;
use Amasty\ReportBuilder\Model\ReportRegistry;
use Amasty\ReportBuilder\Ui\Component\Listing\View\Columns\ColumnFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Columns extends \Magento\Ui\Component\Listing\Columns
{
    /**
     * @var ColumnFactory
     */
    private $columnFactory;

    /**
     * @var Provider
     */
    private $provider;

    /**
     * @var ReportRegistry
     */
    private $reportRegistry;

    public function __construct(
        ContextInterface $context,
        ColumnFactory $columnFactory,
        Provider $provider,
        ReportRegistry $reportRegistry,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->columnFactory = $columnFactory;
        $this->provider = $provider;
        $this->reportRegistry = $reportRegistry;
    }

    public function prepare()
    {
        $scheme = $this->provider->getEntityScheme();
        $report = $this->reportRegistry->getReport();
        foreach ($report->getAllColumns() as $id => $columnData) {
            $config = [];
            $columnScheme = $scheme->getColumnById($id);
            $name = $columnScheme->getAlias();
            if (!isset($this->components[$name]) && $columnData[ColumnInterface::VISIBILITY]) {
                $config['sortOrder'] = $columnData[ColumnInterface::POSITION];
                $config['headerTmpl'] = 'Amasty_ReportBuilder/grid/columns/header';
                $column = $this->columnFactory->create($columnData, $columnScheme, $this->getContext(), $config);
                $column->prepare();
                $this->addComponent($name, $column);
            }
        }

        parent::prepare();
    }
}
