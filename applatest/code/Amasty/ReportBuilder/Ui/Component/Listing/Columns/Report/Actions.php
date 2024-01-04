<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Ui\Component\Listing\Columns\Report;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;

class Actions extends Column
{
    const ACTION_NAME = 'name';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Magento\Framework\Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
        $this->escaper = $escaper;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        foreach (($dataSource['data']['items'] ?? []) as $key => $item) {
            $name = $this->getData(self::ACTION_NAME);
            $item[$name]['edit'] = [
                'href'  => $this->urlBuilder->getUrl(
                    'amreportbuilder/report/edit',
                    [ReportInterface::REPORT_ID => $item[ReportInterface::REPORT_ID]]
                ),
                'label' => __('Edit')
            ];

            $item[$name]['view'] = [
                'href'  => $this->urlBuilder->getUrl(
                    'amreportbuilder/report/view',
                    [ReportInterface::REPORT_ID => $item[ReportInterface::REPORT_ID]]
                ),
                'label' => __('View')
            ];
            $item[$name]['duplicate'] = [
                'href' => $this->urlBuilder->getUrl(
                    'amreportbuilder/report/duplicate',
                    [ReportInterface::REPORT_ID => $item[ReportInterface::REPORT_ID]]
                ),
                'label'   => __('Duplicate')
            ];
            $title = $this->escaper->escapeHtml($item[ReportInterface::NAME]);
            $item[$name]['delete'] = [
                'href'    => $this->urlBuilder->getUrl(
                    'amreportbuilder/report/delete',
                    [ReportInterface::REPORT_ID => $item[ReportInterface::REPORT_ID]]
                ),
                'label'   => __('Delete'),
                'confirm' => [
                    'title'   => __('Delete %1', $title),
                    'message' => __('Are you sure you wan\'t to delete a %1 report?', $title)
                ]
            ];
            $dataSource['data']['items'][$key] = $item;
        }

        return $dataSource;
    }
}
