<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\View;

use Amasty\ReportBuilder\Model\ReportRegistry;
use Amasty\ReportBuilder\Model\Source\IntervalType;
use Amasty\ReportBuilder\Model\View\Ui\Component\Listing\BookmarkProvider;
use Amasty\ReportBuilder\ViewModel\Adminhtml\View\Toolbar;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\Store;

class FiltersProvider
{
    const FILTER_STORE = 'store';
    const FILTER_INTERVAL = 'interval';
    const FILTER_FROM = 'from';
    const FILTER_TO = 'to';
    const DEFAULT_INTERVAL = 'day';

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var ReportRegistry
     */
    private $reportRegistry;

    /**
     * @var BookmarkProvider
     */
    private $bookmarkProvider;

    /**
     * @var array
     */
    private $bookmarkFilters = null;

    public function __construct(
        RequestInterface $request,
        TimezoneInterface $timezone,
        BookmarkProvider $bookmarkProvider,
        ReportRegistry $reportRegistry
    ) {
        $this->request = $request;
        $this->timezone = $timezone;
        $this->bookmarkProvider = $bookmarkProvider;
        $this->reportRegistry = $reportRegistry;
    }

    public function getDateFilter(): array
    {
        $formFilter = $this->getFilter(self::FILTER_FROM);
        $toFilter = $this->getFilter(self::FILTER_TO);

        $filter[self::FILTER_FROM] = $formFilter ?: $this->timezone->date(strtotime(Toolbar::DEFAULT_FROM));
        $filter[self::FILTER_TO] = $toFilter ?: $this->timezone->date(time());
        
        return $filter;
    }

    public function getInterval(): string
    {
        $filter = $this->getFilter(self::FILTER_INTERVAL);
        if (!$filter) {
            $filter = self::DEFAULT_INTERVAL;
        }

        return (string) $filter;
    }

    public function getStoreId(): int
    {
        $filter = $this->getFilter(self::FILTER_STORE);
        if (!$filter) {
            $storeIds = $this->reportRegistry->getReport()->getStoreIds();
            $filter = $storeIds[0] ?? Store::DEFAULT_STORE_ID;
        }

        return (int) $filter;
    }

    public function getGridDataInterval(): string
    {
        $filter = $this->getFilter(self::FILTER_INTERVAL, 'grid_data');
        if (!$filter) {
            $filter = self::DEFAULT_INTERVAL;
        }

        return (string) $filter;
    }

    public function getBookMarkFilters(): array
    {
        if (!$this->bookmarkFilters) {
            $filters = [];
            $report = $this->reportRegistry->getReport();
            $bookmarks = $this->bookmarkProvider->execute($report->getReportId());
            foreach ($bookmarks as $bookmark) {
                $config = $bookmark->getConfig();
                $configFilters = $config['current']['filters'] ?? [];
                if (!isset($filters[self::FILTER_FROM]) && isset($configFilters[self::FILTER_FROM])) {
                    $filters[self::FILTER_FROM] = $configFilters[self::FILTER_FROM];
                }
                if (!isset($filters[self::FILTER_TO]) && isset($configFilters[self::FILTER_TO])) {
                    $filters[self::FILTER_TO] = $configFilters[self::FILTER_TO];
                }
                if (!isset($filters[self::FILTER_STORE]) && isset($configFilters[self::FILTER_STORE])) {
                    $filters[self::FILTER_STORE] = $configFilters[self::FILTER_STORE];
                }
                if (!isset($filters[self::FILTER_INTERVAL]) && isset($configFilters[self::FILTER_INTERVAL])) {
                    $filters[self::FILTER_INTERVAL] = $configFilters[self::FILTER_INTERVAL];
                }
            }
            $this->bookmarkFilters = $filters;
        }

        return $this->bookmarkFilters;
    }

    public function getFilter(string $filterName, string $paramName = 'filters')
    {
        $filters = $this->request->getParams($paramName, []);
        $bookmarkFilters = $this->getBookMarkFilters();

        return $filters[$filterName] ?? $bookmarkFilters[$filterName] ?? null;
    }
}
