<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Ui\Component\Listing\View\Buttons;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Model\ReportRegistry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class EditButton implements ButtonProviderInterface
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

    public function getButtonData()
    {
        $data = [];
        $reportId = $this->registry->getReport()->getReportId();
        if ($reportId) {
            $data = [
                'label' => __('Edit report'),
                'class' => 'edit action-secondary',
                'url' => $this->urlBuilder->getUrl('*/report/edit', [ReportInterface::REPORT_ID => $reportId]),
                'sort_order' => 20,
                'aclResource' => 'Amasty_ReportBuilder::report_edit'
            ];
        }

        return $data;
    }
}
