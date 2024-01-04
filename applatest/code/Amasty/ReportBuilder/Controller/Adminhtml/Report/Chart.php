<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Controller\Adminhtml\Report;

use Amasty\ReportBuilder\Model\ReportResolver;
use Amasty\ReportBuilder\Model\View\ChartConfig;
use Amasty\ReportBuilder\Model\View\ChartFilterResolver;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Chart extends Action implements HttpPostActionInterface
{
    const ADMIN_RESOURCE = 'Amasty_ReportBuilder::report_edit';
    const PARAM_NAME = 'report_id';
    const GRID_DATA_PARAM_NAME = 'grid_data';

    /**
     * @var ReportResolver
     */
    private $reportResolver;

    /**
     * @var ChartConfig
     */
    private $chartConfig;

    /**
     * @var ChartFilterResolver
     */
    private $filterResolver;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var Json
     */
    private $resultJson;

    public function __construct(
        Context $context,
        ReportResolver $reportResolver,
        ChartConfig $chartConfig,
        ChartFilterResolver $filterResolver,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->reportResolver = $reportResolver;
        $this->chartConfig = $chartConfig;
        $this->filterResolver = $filterResolver;
        $this->logger = $logger;
    }

    public function execute()
    {
        $reportId = (int) $this->getRequest()->getParam(self::PARAM_NAME);
        $chartData = [];

        try {
            $report = $this->reportResolver->resolve($reportId);
            $this->filterResolver->resolveFilters($report, $this->getFilterParams());
            $chartData = $this->chartConfig->getChartConfig($report->getReportId());
        } catch (LocalizedException $e) {
            $this->logger->debug($e->getMessage());
        }

        $this->resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        return $this->resultJson->setData($chartData);
    }

    private function getFilterParams(): array
    {
        $filterParams = [];
        $gridData = $this->getRequest()->getParam(self::GRID_DATA_PARAM_NAME);
        if (isset($gridData['filters']['applied']) && !empty($gridData['filters']['applied'])) {
            foreach ($gridData['filters']['applied'] as $filterKey => $filter) {
                if ($filterKey == 'placeholder') {
                    continue;
                }
                $filterParams[$filterKey] = !is_array($filter) ? ['value' => $filter] : $filter;
            }
        }

        return $filterParams;
    }
}
