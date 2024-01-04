<?php

namespace Amasty\RmaAutomation\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class ItemsResolutions
 */
class ItemsResolutions implements OptionSourceInterface
{
    /**
     * @var \Amasty\Rma\Model\Resolution\ResourceModel\CollectionFactory
     */
    private $collection;

    public function __construct(
        \Amasty\Rma\Model\Resolution\ResourceModel\CollectionFactory $collection
    ) {
        $this->collection = $collection;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        /** @var \Amasty\Rma\Model\Resolution\ResourceModel\Collection $collection */
        $collection = $this->collection->create()->addNotDeletedFilter();
        $result = [];

        /** @var \Amasty\Rma\Model\Resolution\Resolution $resolution */
        foreach ($collection->getItems() as $resolution) {
            $result[] = ['value' => $resolution->getResolutionId(), 'label' => __($resolution->getTitle())];
        }

        return $result;
    }
}
