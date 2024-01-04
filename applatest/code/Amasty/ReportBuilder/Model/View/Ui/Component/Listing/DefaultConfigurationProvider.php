<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\View\Ui\Component\Listing;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Model\ReportResolver;
use Amasty\ReportBuilder\Model\SelectResolver\Context;
use Amasty\Base\Model\Serializer;
use Amasty\ReportBuilder\Model\View\FiltersProvider;
use Amasty\ReportBuilder\ViewModel\Adminhtml\View\Toolbar;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\Store;

class DefaultConfigurationProvider
{
    /**
     * @var ReportResolver
     */
    private $reportResolver;

    /**
     * @var \Amasty\ReportBuilder\Model\EntityScheme\Provider
     */
    private $schemeProvider;

    /**
     * @var FiltersProvider
     */
    private $filtersProvider;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    public function __construct(
        Context $context,
        FiltersProvider $filtersProvider,
        Serializer $serializer,
        TimezoneInterface $timezone
    ) {
        $this->reportResolver = $context->getReportResolver();
        $this->schemeProvider = $context->getEntitySchemeProvider();
        $this->filtersProvider = $filtersProvider;
        $this->serializer = $serializer;
        $this->timezone = $timezone;
    }

    public function execute(): array
    {
        $report = $this->reportResolver->resolve();
        if (!$report->getReportId()) {
            return [];
        }

        $config = $this->getDefaultConfig();
        $this->modifyFilters($config);
        $this->modifyOrders($config);
        $this->modifyToolbarFilters($config);

        return $config;
    }

    private function getDefaultConfig(): array
    {
        $config = [];
        $scheme = $this->schemeProvider->getEntityScheme();
        $report = $this->reportResolver->resolve();
        $counter = 0;
        foreach ($report->getAllColumns() as $columnId => $columnData) {
            $column = $scheme->getColumnById($columnId);
            $config['current']['columns'][$column->getAlias()]['sorting'] = false;
            $config['current']['columns'][$column->getAlias()]['visible'] = true;
            $config['current']['positions'][$column->getAlias()] = $counter++;
        }

        if (!isset($config['current']['filters'])) {
            $config['current']['filters']['applied']['placeholder'] = true;
        }
        $config['current']['displayMode'] = 'grid';

        return $config;
    }

    private function modifyFilters(&$config): void
    {
        $report = $this->reportResolver->resolve();
        $scheme = $this->schemeProvider->getEntityScheme();
        foreach ($report->getAllColumns() as $columnId => $columnData) {
            if (isset($columnData[ColumnInterface::FILTER]) && $columnData[ColumnInterface::FILTER]) {
                $filter = $columnData[ColumnInterface::FILTER];
                if (!is_array($filter)) {
                    $filter = $this->serializer->unserialize($filter);
                }
                
                $column = $scheme->getColumnById($columnId);
                $config['current']['filters']['applied'][$column->getAlias()] = $filter['value'] ?? $filter;
            }
        }
    }

    private function modifyToolbarFilters(&$config): void
    {
        $config['current']['filters'][FiltersProvider::FILTER_STORE] = $this->getDefaultStoreId();
        $config['current']['filters'][FiltersProvider::FILTER_INTERVAL] = FiltersProvider::DEFAULT_INTERVAL;
        $from = $this->timezone->date(strtotime(Toolbar::DEFAULT_FROM));
        $config['current']['filters'][FiltersProvider::FILTER_FROM] = $from;
        $config['current']['filters'][FiltersProvider::FILTER_TO] = $this->timezone->date(time());
    }

    private function modifyOrders(&$config): void
    {
        $report = $this->reportResolver->resolve();
        $scheme = $this->schemeProvider->getEntityScheme();
        $columnId = $report->getSortingColumnId();
        if ($columnId) {
            $alias = $scheme->getColumnById($columnId)->getAlias();
        } else {
            $mainEntity = $scheme->getEntityByName($report->getMainEntity());
            $alias = $mainEntity->getPrimaryColumn()->getAlias();
            if ($report->getUsePeriod()) {
                $alias = $mainEntity->getPeriodColumn()->getAlias();
            }
        }

        $config['current']['columns'][$alias]['sorting'] = strtolower($report->getSortingColumnExpression());
    }

    private function getDefaultStoreId(): int
    {
        $storeIds = $this->reportResolver->resolve()->getStoreIds();
        return isset($storeIds[0]) ? (int) $storeIds[0] : Store::DEFAULT_STORE_ID;
    }
}
