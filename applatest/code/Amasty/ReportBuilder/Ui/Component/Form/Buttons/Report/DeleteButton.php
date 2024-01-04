<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Ui\Component\Form\Buttons\Report;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton implements ButtonProviderInterface
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * Registry
     *
     * @var \Amasty\ReportBuilder\Model\ReportRegistry
     */
    private $registry;

    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Amasty\ReportBuilder\Model\ReportRegistry $registry
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->registry = $registry;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $data = [];
        $reportId = $this->registry->getReport()->getReportId();
        if ($reportId) {
            $url = $this->urlBuilder->getUrl('*/*/delete', [ReportInterface::REPORT_ID => $reportId]);
            $data = [
                'label'      => __('Delete'),
                'class'      => 'delete',
                'on_click'   => 'deleteConfirm(\''
                    . __('Are you sure you want to delete this report?') . '\', \'' . $url . '\')',
                'sort_order' => 20,
                'aclResource' => 'Amasty_ReportBuilder::report_delete'
            ];
        }

        return $data;
    }
}
