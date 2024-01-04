<?php

namespace Amasty\RmaAutomation\Model\OptionSource;

use Amasty\Rma\Model\Status\ResourceModel\Collection;
use Amasty\Rma\Model\Status\ResourceModel\CollectionFactory;
use Amasty\Rma\Model\Status\Status;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class StatusAction
 */
class StatusAction implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $statusCollectionFactory;

    public function __construct(
        CollectionFactory $statusCollectionFactory
    ) {
        $this->statusCollectionFactory = $statusCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        /** @var Collection $collection */
        $collection = $this->statusCollectionFactory->create()->addNotDeletedFilter();
        $result = [];

        /** @var Status $status */
        foreach ($collection->getItems() as $status) {
            $result[] = ['value' => $status->getStatusId(), 'label' => __($status->getTitle())];
        }

        return $result;
    }
}
