<?php

namespace Amasty\RmaAutomation\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class ItemsConditions
 */
class ItemsConditions implements OptionSourceInterface
{
    /**
     * @var \Amasty\Rma\Model\Reason\ResourceModel\CollectionFactory
     */
    private $collection;

    public function __construct(
        \Amasty\Rma\Model\Condition\ResourceModel\CollectionFactory $collection
    ) {
        $this->collection = $collection;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        /** @var \Amasty\Rma\Model\Condition\ResourceModel\Collection $collection */
        $collection = $this->collection->create()->addNotDeletedFilter();
        $result = [];

        /** @var \Amasty\Rma\Model\Condition\Condition $condition */
        foreach ($collection->getItems() as $condition) {
            $result[] = ['value' => $condition->getConditionId(), 'label' => __($condition->getTitle())];
        }

        return $result;
    }
}
