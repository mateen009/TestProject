<?php

namespace Amasty\RmaAutomation\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class ItemsReason
 */
class ItemsReason implements OptionSourceInterface
{
    /**
     * @var \Amasty\Rma\Model\Reason\ResourceModel\CollectionFactory
     */
    private $collection;

    public function __construct(
        \Amasty\Rma\Model\Reason\ResourceModel\CollectionFactory $collection
    ) {
        $this->collection = $collection;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        /** @var \Amasty\Rma\Model\Reason\ResourceModel\Collection $collection */
        $collection = $this->collection->create()->addNotDeletedFilter();
        $result = [];

        /** @var \Amasty\Rma\Model\Reason\Reason $reason */
        foreach ($collection->getItems() as $reason) {
            $result[] = ['value' => $reason->getReasonId(), 'label' => __($reason->getTitle())];
        }

        return $result;
    }
}
