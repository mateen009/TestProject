<?php

namespace Amasty\RmaAutomation\Model\AutomationRule\Condition;

use Amasty\Rma\Api\Data\HistoryInterface;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;

/**
 * Class Rma
 */
class Rma extends AbstractCondition
{
    /**#@+
     * Constants defined for attributes keys
     */
    const CURRENT_STATUS = 'current_status';

    const CURRENT_MANAGER = 'current_manager';

    const LAST_UPDATE = 'last_update';

    const LAST_UPDATE_BY = 'last_update_by';

    const ITEMS_HAVE_REASON = 'items_reason';

    const ITEMS_HAVE_CONDITION = 'items_condition';

    const ITEMS_HAVE_RESOLUTION = 'items_resolution';

    const ITEMS_TOTAL_PRICE = 'items_total_price';

    /**#@-*/

    /**
     * @var \Amasty\RmaAutomation\Model\OptionSource\StatusAction
     */
    private $status;

    /**
     * @var \Amasty\Rma\Model\OptionSource\Manager
     */
    private $manager;

    /**
     * @var \Amasty\RmaAutomation\Model\OptionSource\UpdatedBy
     */
    private $updatedBy;

    /**
     * @var \Amasty\RmaAutomation\Model\OptionSource\ItemsReason
     */
    private $itemsReason;

    /**
     * @var \Amasty\RmaAutomation\Model\OptionSource\ItemsResolutions
     */
    private $itemsResolutions;

    /**
     * @var \Amasty\RmaAutomation\Model\OptionSource\ItemsConditions
     */
    private $itemsConditions;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory
     */
    private $orderItemCollectionFactory;

    /**
     * @var \Amasty\Rma\Model\History\ResourceModel\CollectionFactory
     */
    private $historyCollectionFactory;

    public function __construct(
        \Amasty\Rma\Model\OptionSource\Manager $manager,
        \Amasty\RmaAutomation\Model\OptionSource\StatusAction $status,
        \Amasty\RmaAutomation\Model\OptionSource\UpdatedBy $updatedBy,
        \Amasty\RmaAutomation\Model\OptionSource\ItemsReason $itemsReason,
        \Amasty\RmaAutomation\Model\OptionSource\ItemsResolutions $itemsResolutions,
        \Amasty\RmaAutomation\Model\OptionSource\ItemsConditions $itemsConditions,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
        \Amasty\Rma\Model\History\ResourceModel\CollectionFactory $historyCollectionFactory,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->manager = $manager;
        $this->status = $status;
        $this->updatedBy = $updatedBy;
        $this->itemsReason = $itemsReason;
        $this->itemsResolutions = $itemsResolutions;
        $this->itemsConditions = $itemsConditions;
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->historyCollectionFactory = $historyCollectionFactory;
    }

    /**
     * @return AbstractCondition
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            self::CURRENT_STATUS        => __('Current Status'),
            self::CURRENT_MANAGER       => __('Current Manager'),
            self::LAST_UPDATE           => __('Last Update (hours)'),
            self::LAST_UPDATE_BY        => __('Last Update by'),
            self::ITEMS_HAVE_REASON     => __('Items Reason'),
            self::ITEMS_HAVE_CONDITION  => __('Items Condition'),
            self::ITEMS_HAVE_RESOLUTION => __('Items Resolution'),
            self::ITEMS_TOTAL_PRICE     => __('Items Total Price')
        ];
        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * @return AbstractCondition
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);

        return $element;
    }

    /**
     * @return string
     */
    public function getValueElementType()
    {
        $attribute = 'text';

        switch ($this->getAttribute()) {
            case self::CURRENT_STATUS:
            case self::CURRENT_MANAGER:
            case self::LAST_UPDATE_BY:
            case self::ITEMS_HAVE_REASON:
            case self::ITEMS_HAVE_CONDITION:
            case self::ITEMS_HAVE_RESOLUTION:
                $attribute = 'select';
                break;
            case self::LAST_UPDATE:
            case self::ITEMS_TOTAL_PRICE:
                $attribute = 'text';
                break;
        }

        return $attribute;
    }

    /**
     * @return array
     */
    public function getValueSelectOptions()
    {
        $key = 'value_select_options';
        $selectOptions = [];

        switch ($this->getAttribute()) {
            case self::CURRENT_STATUS:
                $selectOptions = $this->status->toOptionArray();
                break;
            case self::CURRENT_MANAGER:
                $selectOptions = $this->manager->toOptionArray();
                break;
            case self::LAST_UPDATE_BY:
                $selectOptions = $this->updatedBy->toOptionArray();
                break;
            case self::ITEMS_HAVE_REASON:
                $selectOptions = $this->itemsReason->toOptionArray();
                break;
            case self::ITEMS_HAVE_CONDITION:
                $selectOptions = $this->itemsConditions->toOptionArray();
                break;
            case self::ITEMS_HAVE_RESOLUTION:
                $selectOptions = $this->itemsResolutions->toOptionArray();
                break;
        }

        if (!$this->hasData($key)) {
            $this->setData($key, $selectOptions);
        }

        return $this->getData($key);
    }

    /**
     * Validate Rma Conditions
     *
     * @param \Magento\Framework\Model\AbstractModel|\Amasty\Rma\Model\Request\Request $model
     *
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $attributeValue = 0;

        switch ($this->getAttribute()) {
            case self::CURRENT_STATUS:
                $attributeValue = $model->getStatus();
                break;
            case self::CURRENT_MANAGER:
                $attributeValue = $model->getManagerId();
                break;
            case self::LAST_UPDATE:
                $attributeValue = $model->getModifiedAt();
                break;
            case self::LAST_UPDATE_BY:
                $historyCollection = $this->historyCollectionFactory->create()
                    ->addRequestFilter($model->getId())
                    ->addOrder(HistoryInterface::EVENT_ID);
                $attributeValue = $historyCollection->getFirstItem()->getEventInitiator();
                break;
            case self::ITEMS_HAVE_RESOLUTION:
                $attributeValue = $this->getRequestResolutions($model);
                break;
            case self::ITEMS_HAVE_CONDITION:
                $attributeValue = $this->getRequestConditions($model);
                break;
            case self::ITEMS_HAVE_REASON:
                $attributeValue = $this->getRequestReasons($model);
                break;
            case self::ITEMS_TOTAL_PRICE:
                $attributeValue = $this->getRequestItemsTotalPrice($model);
                break;
        }

        return $this->validateAttribute($attributeValue);
    }

    /**
     * @param \Amasty\Rma\Model\Request\Request $request
     *
     * @return array
     */
    private function getRequestResolutions($request)
    {
        $resolutions = [];
        $requestItems = $request->getRequestItems();

        /** @var \Amasty\Rma\Model\Request\RequestItem $requestItem */
        foreach ($requestItems as $requestItem) {
            $resolutions[] = $requestItem->getResolutionId();
        }

        return array_unique($resolutions);
    }

    /**
     * @param \Amasty\Rma\Model\Request\Request $request
     *
     * @return array
     */
    private function getRequestConditions($request)
    {
        $conditions = [];
        $requestItems = $request->getRequestItems();

        /** @var \Amasty\Rma\Model\Request\RequestItem $requestItem */
        foreach ($requestItems as $requestItem) {
            $conditions[] = $requestItem->getConditionId();
        }

        return array_unique($conditions);
    }

    /**
     * @param \Amasty\Rma\Model\Request\Request $request
     *
     * @return array
     */
    private function getRequestReasons($request)
    {
        $reasons = [];
        $requestItems = $request->getRequestItems();

        /** @var \Amasty\Rma\Model\Request\RequestItem $requestItem */
        foreach ($requestItems as $requestItem) {
            $reasons[] = $requestItem->getReasonId();
        }

        return array_unique($reasons);
    }

    /**
     * @param \Amasty\Rma\Model\Request\Request $request
     *
     * @return string
     */
    private function getRequestItemsTotalPrice($request)
    {
        $requestItemsQty = [];

        foreach ($request->getRequestItems() as $requestItem) {
            $requestItemsQty[$requestItem->getOrderItemId()] = $requestItem->getRequestQty();
        }
        $orderItemCollection = $this->orderItemCollectionFactory->create()
            ->addFieldToSelect(
                ['item_id', 'parent_item_id']
            )->addFieldToFilter('item_id', ['in' => array_keys($requestItemsQty)]);
        $items = $orderItemCollection->getConnection()->fetchAssoc($orderItemCollection->getSelect());
        $itemsQty = [];

        foreach ($items as $itemData) {
            $itemId = !empty($itemData['parent_item_id']) ? $itemData['parent_item_id'] : $itemData['item_id'];
            $itemsQty[$itemId] = $requestItemsQty[$itemData['item_id']];
        }
        $priceCollection = $this->orderItemCollectionFactory->create()
            ->addFieldToSelect(['item_id', 'price'])
            ->addFieldToFilter('item_id', ['in' => array_keys($itemsQty)]);
        $prices = $priceCollection->getConnection()->fetchAssoc($priceCollection->getSelect());
        $totalPrice = 0;

        foreach ($prices as $itemId => $price) {
            $totalPrice += $price['price'] * $itemsQty[$itemId];
        }

        return $totalPrice;
    }
}
