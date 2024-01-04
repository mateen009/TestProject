<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\Catalog\Model\Product;

/**
 * Class InstallData
 * @package Magenest\RentalSystem\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * InstallData constructor.
     *
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributeRepositoryInterface $attributeRepository
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeRepositoryInterface $attributeRepository,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository   = $attributeRepository;
        $this->eavSetupFactory       = $eavSetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $fieldList = [
            'price',
//            'special_price',
//            'special_from_date',
//            'special_to_date',
//            'minimal_price',
//            'cost',
//            'tier_price',
            'tax_class_id',
            'weight',
            'country_of_manufacture'
        ];

        $searchCriteria      = $this->searchCriteriaBuilder->addFilter('is_user_defined', 1)->create();
        $attributeRepository = $this->attributeRepository->getList(
            Product::ENTITY,
            $searchCriteria
        );

        foreach ($attributeRepository->getItems() as $items) {
            $fieldList[] = $items->getAttributeCode();
        }

        foreach ($fieldList as $field) {
            $applyTo = explode(
                ',',
                $eavSetup->getAttribute(Product::ENTITY, $field, 'apply_to')
            );
            if (!in_array('rental', $applyTo)) {
                $applyTo[] = 'rental';
                $eavSetup->updateAttribute(Product::ENTITY, $field, 'apply_to', implode(',', $applyTo));
            }
        }
    }
}
