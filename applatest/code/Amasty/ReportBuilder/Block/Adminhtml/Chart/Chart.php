<?php
namespace Amasty\ReportBuilder\Block\Adminhtml\Chart;
use Amasty\ReportBuilder\Model\Report;

use Amasty\ReportBuilder\Model\ReportResolver;
use Amasty\ReportBuilder\Model\View\ChartConfig;
use Amasty\ReportBuilder\Model\View\ChartFilterResolver;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Chart extends \Magento\Backend\Block\Template
{
	const PARAM_NAME = 'report_id';
    const GRID_DATA_PARAM_NAME = 'grid_data';
	/**
 	* Block template.
 	*
 	* @var string
 	*/
	protected $_template = 'view/am4chart.phtml';

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

	protected $report;

	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		Report $report,
		ReportResolver $reportResolver,
        ChartConfig $chartConfig,
        ChartFilterResolver $filterResolver,
        LoggerInterface $logger,
		array $data = []
	)
	{
		parent::__construct($context, $data);
		$this->report = $report;
		$this->reportResolver = $reportResolver;
        $this->chartConfig = $chartConfig;
        $this->filterResolver = $filterResolver;
        $this->logger = $logger;
	}

	public function getChartData(){
		$reportId = (int) $this->getRequest()->getParam(self::PARAM_NAME);
        $chartData = [];

        try {
            $report = $this->reportResolver->resolve($reportId);
            $this->filterResolver->resolveFilters($report, $this->getFilterParams());
			$custom_chart = true;
            $chartData = $this->chartConfig->getChartConfig($report->getReportId(), $custom_chart);
        } catch (LocalizedException $e) {
            $this->logger->debug($e->getMessage());
        }
		return $chartData;
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

    public function isDisplayChart(){
		$reportId = (int) $this->getRequest()->getParam(self::PARAM_NAME);
        $report = $this->reportResolver->resolve($reportId);
        if ($report->getDisplayChart() == 1){
            return true;
        } else {
            return false;
        }
    }

}