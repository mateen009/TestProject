<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Ui\Component\Form\Buttons\Report;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Model\ReportRegistry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DuplicateButton implements ButtonProviderInterface
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ReportRegistry
     */
    private $registry;

    public function __construct(
        UrlInterface $urlBuilder,
        ReportRegistry $registry
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $reportId = $this->registry->getReport()->getReportId();

        if ($reportId) {
            $url = $this->urlBuilder->getUrl('*/*/duplicate', [ReportInterface::REPORT_ID => $reportId]);
            $data = [
                'label' => __('Duplicate'),
                'class' => 'save',
                'on_click' => sprintf("location.href = '%s';", $url),
                'sort_order'  => 100,
                'aclResource' => 'Amasty_ReportBuilder::report_duplicate'
            ];
        } else {
            $data = [];
        }

        return $data;
    }
}
