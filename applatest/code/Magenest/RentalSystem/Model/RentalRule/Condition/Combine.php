<?php
namespace Magenest\RentalSystem\Model\RentalRule\Condition;

use Magento\CatalogRule\Model\Rule\Condition\Product;
use Magento\Rule\Model\Condition\Context;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * Combine constructor.
     * @param Context $context
     * @param array $data
     */
    public function __construct(Context $context, array $data = [])
    {
        parent::__construct($context, $data);
        $this->setType(Combine::class);
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $attributes = [
            [
                'value' => 'Magento\CatalogRule\Model\Rule\Condition\Product|category_ids',
                'label' => __('Category (Only Rental products)'),
            ],
            [
                'value' => 'Magenest\RentalSystem\Model\RentalRule\Condition\Product|sku',
                'label' => __('Sku'),
            ]
        ];
        $conditions = parent::getNewChildSelectOptions();

        return array_merge_recursive(
            $conditions,
            [
                [
                    'value' => Combine::class,
                    'label' => __('Conditions Combination'),
                ],
                ['label' => __('Product Attribute'), 'value' => $attributes]
            ]
        );
    }

    /**
     * @param array $productCollection
     * @return Combine
     */
    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            /** @var Product|Combine $condition */
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }
}
