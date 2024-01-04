<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\View;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Column\OptionsResolver;
use Amasty\ReportBuilder\Model\EntityScheme\Provider;
use Amasty\ReportBuilder\Model\ReportResolver;
use Amasty\ReportBuilder\Model\ResourceModel\Report\Data\Collection;
use Amasty\ReportBuilder\Model\ResourceModel\Report\Data\CollectionFactory;
use Amasty\ReportBuilder\Model\Source\IntervalType;
use Amasty\ReportBuilder\Model\View\Ui\Component\Listing\BookmarkProvider;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;

class ChartConfig implements ArgumentInterface
{
    const AXIS_TYPE_TEXT = 'text';

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ReportResolver
     */
    private $resolver;

    /**
     * @var FilterApplier
     */
    private $filterApplier;

    /**
     * @var FiltersProvider
     */
    private $filtersProvider;

    /**
     * @var Provider
     */
    private $provider;

    /**
     * @var OptionsResolver
     */
    private $optionsResolver;

    /**
     * @var UiComponentFactory
     */
    private $uiFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var BookmarkProvider
     */
    private $bookmarkProvider;

    public function __construct(
        CollectionFactory $collectionFactory,
        ReportResolver $resolver,
        FilterApplier $filterApplier,
        FiltersProvider $filtersProvider,
        Provider $provider,
        OptionsResolver $optionsResolver,
        UiComponentFactory $uiFactory,
        RequestInterface $request,
        BookmarkProvider $bookmarkProvider
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->resolver = $resolver;
        $this->filterApplier = $filterApplier;
        $this->filtersProvider = $filtersProvider;
        $this->provider = $provider;
        $this->optionsResolver = $optionsResolver;
        $this->uiFactory = $uiFactory;
        $this->request = $request;
        $this->bookmarkProvider = $bookmarkProvider;
    }

    public function getChartConfig(int $reportId = null, $custom_chart = null): array
    {
        $report = $this->resolver->resolve($reportId);
        if ($custom_chart) {
            $chartData = $this->getChartData(null, $custom_chart);
        } else {
            $chartData = $this->getChartData();
        }
        $config = [
            'data' => $chartData,
            'xAxisType' => $this->getAxisType($report->getChartAxisX()),
            'yAxisType' => $this->getAxisType($report->getChartAxisY()),
            'interval' => $this->filtersProvider->getGridDataInterval()
        ];

        return $config;
    }

    public function getChartData(int $reportId = null, $custom_chart = null): array
    {
        $report = $this->resolver->resolve($reportId);
        $scheme = $this->provider->getEntityScheme();
        $axisX = $scheme->getColumnById($report->getChartAxisX());
        $axisXOptions = $this->optionsResolver->resolve($axisX);
        $axisY = $scheme->getColumnById($report->getChartAxisY());
        $axisYOptions = $this->optionsResolver->resolve($axisY);

        $chartData = [];
        if ($custom_chart) {
            foreach ($this->getCollection($custom_chart) as $item) {
                $chartData[] = [
                    'valueX' => $this->getItemData($axisX, $item, $axisXOptions),
                    'valueY' => $this->getItemData($axisY, $item, $axisYOptions),
                ];
            }
        } else {
            foreach ($this->getCollection() as $item) {
                $chartData[] = [
                    'valueX' => $this->getItemData($axisX, $item, $axisXOptions),
                    'valueY' => $this->getItemData($axisY, $item, $axisYOptions),
                ];
            }
        }

        return $chartData;
    }

    private function getCollection($custom_chart = null): Collection
    {
        $filters = [];
        $bookmarks = $this->bookmarkProvider->execute($this->resolver->resolve()->getReportId());
        foreach ($bookmarks as $bookmark) {
            if (isset($bookmark->getConfig()['current']['filters']['applied'])) {
                $bookmarkFilters = $bookmark->getConfig()['current']['filters']['applied'];
                // phpcs:ignore
                $filters = array_merge($filters,  $bookmarkFilters);
            }
        }


        $this->request->setParam('filters', $filters);
        $component = $this->uiFactory->create('amreportbuilder_view_listing');
        if (!$custom_chart) {
            $this->prepareComponent($component);
        }
        $collection = $component->getContext()->getDataProvider()->getCollection();
        $collection->setPageSize(null)->setCurPage(null);

        $this->filterApplier->execute($this->resolver->resolve(), $collection);

        return $collection;
    }

    private function prepareComponent(UiComponentInterface $component)
    {
        foreach ($component->getChildComponents() as $child) {
            $this->prepareComponent($child);
        }


        $component->prepare();
    }

    private function getItemData(ColumnInterface $column, DataObject $item, array $options = []): string
    {
        if (in_array($column->getFrontendModel(), ['select', 'multiselect']) && !empty($options)) {
            $aggregatedOptions = explode(',', (string)$item->getData($column->getAlias()));
            $preparedOptions = [];
            foreach ($aggregatedOptions as $option) {
                foreach ($options as $columnOption) {
                    if ($columnOption['value'] == $option) {
                        $preparedOptions[] = $columnOption['label'];
                    }
                }
            }
            if (!$preparedOptions) {
                $preparedOptions = $aggregatedOptions;
            }
            return str_replace('-', ' ', implode(', ', $preparedOptions));
        }

        return str_replace('-', ' ', $item->getData($column->getAlias()));
    }

    private function getAxisType(string $columnId): string
    {
        $scheme = $this->provider->getEntityScheme();
        $columnScheme = $scheme->getColumnById($columnId);

        if (in_array($columnScheme->getFrontendModel(), ['select', 'multiselect'])) {
            return self::AXIS_TYPE_TEXT;
        }

        return $columnScheme->getType();
    }
}
