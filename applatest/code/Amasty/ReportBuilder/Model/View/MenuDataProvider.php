<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\View;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Model\ResourceModel\Report\CollectionFactory as ReportCollectionFactory;
use Amasty\ReportBuilder\Model\ResourceModel\Report\Collection as ReportCollection;
use Magento\Framework\UrlInterface;

class MenuDataProvider
{
    /**
     * @var ReportCollectionFactory
     */
    private $reportCollectionFactory;

    /**
     * @var ReportCollection
     */
    private $reportCollection;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var array
     */
    private $actionList = [];

    public function __construct(
        ReportCollectionFactory $reportCollectionFactory,
        UrlInterface $urlBuilder,
        array $reportActionList = []
    ) {
        $this->reportCollectionFactory = $reportCollectionFactory;
        $this->urlBuilder = $urlBuilder;
        $this->actionList = $reportActionList;
    }

    public function execute(): array
    {
        $menuData = [
            'new_action' => $this->urlBuilder->getUrl('*/report/newAction')
        ];

        foreach ($this->getReportCollection() as $report) {
            $reportData = [
                ReportInterface::REPORT_ID => $report->getReportId(),
                ReportInterface::NAME => $report->getName(),
            ];
            $this->addReportActions($reportData);

            $menuData['items'][] = $reportData;
        }

        return $menuData;
    }

    private function addReportActions(array &$reportData): void
    {
        foreach ($this->actionList as $action => $params) {
            if (!is_array($params)) {
                $params = [];
            }

            if (isset($params['redirect'])) {
                $params['entity_id'] = $this->reportCollection->getFirstItem()->getReportId();
            }

            $params[ReportInterface::REPORT_ID] = $reportData[ReportInterface::REPORT_ID];
            $reportData[$action] = $this->urlBuilder->getUrl('*/*/' . $action, ['_query' => $params]);
        }
    }

    private function getReportCollection(): ReportCollection
    {
        if (!$this->reportCollection) {
            $this->reportCollection = $this->reportCollectionFactory->create();
            $this->reportCollection->setOrder(ReportInterface::REPORT_ID, ReportCollection::SORT_ORDER_ASC);
        }

        return $this->reportCollection;
    }
}
