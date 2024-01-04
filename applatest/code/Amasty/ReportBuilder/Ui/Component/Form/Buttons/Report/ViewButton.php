<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Ui\Component\Form\Buttons\Report;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class ViewButton implements ButtonProviderInterface
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
        $reportId = $this->registry->getReport()->getReportId();

        return [
            'label' => __('Save and View'),
            'class' => 'save',
            'url' => $this->urlBuilder->getUrl('*/*/save', [
                ReportInterface::REPORT_ID => $reportId,
            ]),
            'on_click' => '',
            'sort_order' => 20,
            'aclResource' => 'Amasty_ReportBuilder::report_edit',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'amreportbuilder_report_form.amreportbuilder_report_form',
                                'actionName' => 'save',
                                'params' => [true, ['redirect' => 'view']
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
