<?php

namespace Amasty\RmaAutomation\Model\AutomationRule\DataProvider;

use Amasty\RmaAutomation\Model\AutomationRule\ResourceModel\Collection;
use Amasty\RmaAutomation\Model\AutomationRule\ResourceModel\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class Listing
 */
class Listing extends AbstractDataProvider
{
    public function __construct(
        CollectionFactory $collectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create()->addApplyToColumn();
    }

    /**
     * @param \Magento\Framework\Api\Filter $filter
     *
     * @return mixed|void
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if ($filter->getField() == Collection::APPLY_FOR_FIELD) {
            $filter->setField($this->collection->getApplyToExpression());
        }

        parent::addFilter($filter);
    }
}
