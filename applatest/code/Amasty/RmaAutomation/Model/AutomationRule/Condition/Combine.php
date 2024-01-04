<?php

namespace Amasty\RmaAutomation\Model\AutomationRule\Condition;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;

/**
 * Class Combine
 */
class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @var CustomerFactory
     */
    private $conditionCustomerFactory;

    /**
     * @var CollectionFactory
     */
    private $orderItemCollectionFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * @var RmaFactory
     */
    private $conditionRmaFactory;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\ProductFactory
     */
    private $productConditionFactory;

    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\CatalogRule\Model\Rule\Condition\ProductFactory $productConditionFactory,
        CustomerFactory $conditionCustomerFactory,
        RmaFactory $conditionRmaFactory,
        CollectionFactory $orderItemCollectionFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->setType(\Amasty\RmaAutomation\Model\AutomationRule\Condition\Combine::class);
        $this->conditionCustomerFactory = $conditionCustomerFactory;
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->productRepository = $productRepository;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->conditionRmaFactory = $conditionRmaFactory;
        $this->productConditionFactory = $productConditionFactory;
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $conditions = parent::getNewChildSelectOptions();

        $conditionProductAttributes = $this->productConditionFactory->create()->loadAttributeOptions()->getAttributeOption();
        $productAttributes = [];

        foreach ($conditionProductAttributes as $code => $label) {
            $productAttributes[] = [
                'value' => 'Magento\CatalogRule\Model\Rule\Condition\Product|' . $code,
                'label' => $label,
            ];
        }

        /** @var Customer $condition */
        $conditionCustomer = $this->conditionCustomerFactory->create();
        $conditionCustomerAttributes = $conditionCustomer->loadAttributeOptions()->getAttributeOption();
        $customerAttributes = [];

        foreach ($conditionCustomerAttributes as $code => $label) {
            if ($code == 'lock_expires') {
                $label = 'Lock Expire';
            }
            $customerAttributes[] = [
                'value' => 'Amasty\RmaAutomation\Model\AutomationRule\Condition\Customer|' . $code,
                'label' => $label
            ];
        }

        /** @var Rma $conditionRma */
        $conditionRma = $this->conditionRmaFactory->create();
        $conditionRmaAttributes = $conditionRma->loadAttributeOptions()->getAttributeOption();
        $rmaAttributes = [];

        foreach ($conditionRmaAttributes as $code => $label) {
            $rmaAttributes[] = [
                'value' => 'Amasty\RmaAutomation\Model\AutomationRule\Condition\Rma|' . $code,
                'label' => $label
            ];
        }
        $conditions[] = [
            'value' => \Amasty\RmaAutomation\Model\AutomationRule\Condition\Combine::class,
            'label' => __('Conditions Combination'),
        ];
        $conditions[] = [
            'value' => $productAttributes,
            'label' => __('Product attributes')
        ];
        $conditions[] = [
            'value' => $customerAttributes,
            'label' => __('Customer attributes')
        ];
        $conditions[] = [
            'value' => $rmaAttributes,
            'label' => __('Request Attributes')
        ];

        return $conditions;
    }

    /**
     * @param \Amasty\Rma\Api\Data\RequestInterface $request
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _isValid($request)
    {
        if (!$this->getConditions()) {
            return true;
        }
        $validateAll = $this->getAggregator() === 'all';

        foreach ($this->getConditions() as $cond) {
            $isValid = false;

            switch (true) {
                case $cond instanceof \Magento\CatalogRule\Model\Rule\Condition\Product:
                    $isValid = $this->validateRequestProduct($request, $cond);
                    break;
                case $cond instanceof Customer:
                    $isValid = $this->validateRequestCustomer($request, $cond);
                    break;
                case $cond instanceof Rma:
                    $isValid = $this->validateRequest($request, $cond);
                    break;
            }
            if ($validateAll && !$isValid) {
                return false;
            } elseif (!$validateAll && $isValid) {
                return true;
            }
        }

        return true;
    }

    /**
     * @param \Amasty\Rma\Api\Data\RequestInterface $request
     * @param \Magento\Rule\Model\Condition\AbstractCondition $cond
     *
     * @return bool
     */
    private function validateRequest($request, $cond)
    {
        return $cond->validate($request);
    }

    /**
     * @param \Amasty\Rma\Api\Data\RequestInterface $request
     * @param \Magento\Rule\Model\Condition\AbstractCondition $cond
     *
     * @return bool
     */
    private function validateRequestProduct($request, $cond)
    {
        $orderItemIds = [];
        $validated = false;

        foreach ($request->getRequestItems() as $requestItem) {
            $orderItemIds[] = $requestItem->getOrderItemId();
        }
        $productIds = $this->orderItemCollectionFactory->create()->addFieldToSelect('product_id')
            ->addFieldToFilter('item_id', ['in' => $orderItemIds])
            ->getData();

        foreach ($productIds as $productId) {
            try {
                $product = $this->productRepository->getById($productId['product_id']);
                $validated = $cond->validate($product);
            } catch (NoSuchEntityException $e) {
                $validated = false;
            } catch (\Exception $e) {
                $this->_logger->critical($e);
                $validated = false;
            }
            if (!$validated) {
                break;
            }
        }

        return $validated;
    }

    /**
     * @param \Amasty\Rma\Api\Data\RequestInterface $request
     * @param \Magento\Rule\Model\Condition\AbstractCondition $cond
     *
     * @return bool
     */
    private function validateRequestCustomer($request, $cond)
    {
        $validated = false;

        if ($customerId = (int)$request->getCustomerId()) {
            try {
                $customer = $this->customerCollectionFactory->create()
                    ->addFieldToFilter('entity_id', $customerId)
                    ->getFirstItem();
                if ($customer) {
                    $validated = $cond->validate($customer);
                }
            } catch (\Exception $e) {
                $this->_logger->critical($e);
                $validated = false;
            }
        }

        return $validated;
    }
}
