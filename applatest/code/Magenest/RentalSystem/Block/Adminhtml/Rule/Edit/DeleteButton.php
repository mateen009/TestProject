<?php
namespace Magenest\RentalSystem\Block\Adminhtml\Rule\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @inheritDoc
     */
    public function getButtonData()
    {
        $data = [];
        $ruleId = $this->getRuleId();
        if ($ruleId) {
            $data = [
                'label' => __('Delete Rule'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __('Are you sure you want to do this?') . '\', \''
                    . $this->urlBuilder->getUrl('*/*/delete', ['id' => $ruleId]) . '\', {data: {}})',
                'sort_order' => 20,
            ];
        }
        return $data;
    }
}
