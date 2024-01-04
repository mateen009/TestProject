<?php
namespace Mobility\QuoteRequest\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Indexer\Address\AttributeProvider As customer_address;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateCustomerAddressAttribute implements DataPatchInterface
{
     /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /** @var EavSetupFactory */
    private $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        // $attribute = $eavSetup->getAttribute('customer_address', 'region');
        // $eavSetup->updateAttribute('customer_address', 'region', 'frontend_label', 'State');

        $eavSetup->updateAttribute(\Magento\Customer\Model\Customer::ENTITY, 'send_customers_email', ['is_filterable_in_grid' => true,'is_searchable_in_grid' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
