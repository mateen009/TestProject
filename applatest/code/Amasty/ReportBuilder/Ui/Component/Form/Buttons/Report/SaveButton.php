<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Ui\Component\Form\Buttons\Report;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveButton implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save'),
            'class' => 'save primary',
            'on_click' => '',
            'aclResource' => 'Amasty_ReportBuilder::report_edit'
        ];
    }
}
