<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Ui\DataProvider\Listing\View;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Model\ReportResolver;
use Amasty\ReportBuilder\Model\ResourceModel\Report\Data\CollectionFactory;
use Amasty\ReportBuilder\Model\View\FilterApplier;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ReportResolver
     */
    private $reportResolver;

    /**
     * @var FilterApplier
     */
    private $filterApplier;

    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        ReportResolver $reportResolver,
        FilterApplier $filterApplier,
        $name,
        $primaryFieldName,
        $requestFieldName,
        UrlInterface $urlBuilder,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->request = $request;
        $this->prepareUpdateUrl();
        $this->reportResolver = $reportResolver;
        $this->filterApplier = $filterApplier;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return \Amasty\ReportBuilder\Model\ResourceModel\Report\Data\Collection
     */
    public function getCollection()
    {
        $reportId = (int)$this->request->getParam(ReportInterface::REPORT_ID);
        $report = $this->reportResolver->resolve($reportId);
        $this->prepareConfig($report);
        $this->collection->setReportId($report->getReportId());
        $this->filterApplier->execute($report, $this->collection);

        return $this->collection;
    }

    protected function prepareUpdateUrl(): void
    {
        if (!isset($this->data['config']['filter_url_params'])) {
            return;
        }
        foreach ($this->data['config']['filter_url_params'] as $paramName => $paramValue) {
            if ('*' == $paramValue) {
                $paramValue = $this->request->getParam($paramName);
            }
            if ($paramValue) {
                $this->data['config']['update_url'] = sprintf(
                    '%s%s/%s/',
                    $this->data['config']['update_url'],
                    $paramName,
                    $paramValue
                );
            }
        }
    }

    private function prepareConfig(ReportInterface $report): void
    {
        $this->data['config']['report_id'] = $report->getReportId();
        $this->data['config']['display_chart'] = $report->getDisplayChart();
        $this->data['config']['chart_update_url'] = $this->urlBuilder->getUrl('*/*/chart');
    }

    /**
     * @param array|string $field
     * @param null $alias
     * @return void|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addField($field, $alias = null)
    {
        return null;
    }
}
