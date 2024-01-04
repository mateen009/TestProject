<?php

namespace AscentDigital\NetsuiteConnector\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Config;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CustomerCustomAttributes implements DataPatchInterface
{
    const ATTRIBUTES = [
        'msg_account_manager',
        'alwd_depot_service_type'
    ];

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * CustomerCustomAttribute constructor.
     * @param ModuleDataSetupInterface $setup
     * @param Config $eavConfig
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        Config $eavConfig,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->setup = $setup;
        $this->eavConfig = $eavConfig;
    }

    /** We'll add our customer attribute here */
    public function apply()
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->setup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType(Customer::ENTITY);
        $attributeSetId = $customerSetup->getDefaultAttributeSetId($customerEntity->getEntityTypeId());
        $attributeGroup = $customerSetup->getDefaultAttributeGroupId($customerEntity->getEntityTypeId(), $attributeSetId);

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'msg_account_manager',
            [
                'type'         => 'varchar',
                'label'        => 'MCG Account Manager',
                'input'        => 'text',
                'required'     => false,
                'visible'      => true,
                'user_defined' => true,
                'position'     => 300,
                'system'       => 0,
            ]
        );
        $customerSetup->addAttribute(Customer::ENTITY, 'alwd_depot_service_type', [
            'type'         => 'varchar',
            'label'        => 'Depot Service Type',
            'input'        => 'text',
            'required'     => false,
            'visible'      => true,
            'user_defined' => true,
            'position'     => 310,
            'system'       => 0,
            'unique' => true,
        ]);
        $customerSetup->addAttribute(Customer::ENTITY, 'alwd_depot_service_type', [
            'type' => 'text',
            'label' => 'Depot Service Type',
            'input' => 'multiselect',
            'required' => false,
            'visible' => true,
            'user_defined' => true,
            'sort_order' => 310,
            'position' => 310,
            'system' => 0,
            'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
            'source' => 'AscentDigital\NetsuiteConnector\Model\Customer\Attribute\Source\AllowedDepotServiceType'
        ]);


        foreach (self::ATTRIBUTES as $attributeCode) {
            $attribute = $this->eavConfig->getAttribute(Customer::ENTITY, $attributeCode);
            $attribute->addData([
                'used_in_forms' => ['adminhtml_customer'],
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroup
            ]);
            $attribute->save();
        }
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
