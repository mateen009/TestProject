<?php

namespace AscentDigital\NetsuiteConnector\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Config;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CustomerCustomAttribute implements DataPatchInterface
{
	const ATTRIBUTES = [
		'ns_internal_id',
		'customer_approval',
		'attu_id'
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
    )
    {
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

		$customerSetup->addAttribute(Customer::ENTITY, 'customer_approval', [
			'type' 		   => 'int',
			'input' 	   => 'select',
			'label' 	   => 'Customer Approval',
			'source' 	   => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
			'required'     => false,
			'visible'      => true,
			'user_defined' => true,
			'position'     => 213,
			'default' 	   => '0',
			'system'       => 0,
		]);

        $customerSetup->addAttribute(Customer::ENTITY, 'ns_internal_id', [
            'type'         => 'varchar',
            'label'        => 'NS Internal Id',
            'input'        => 'text',
            'required'     => false,
            'visible'      => true,
            'user_defined' => true,
            'position'     => 214,
            'system'       => 0,
        ]
    );
    $customerSetup->addAttribute(Customer::ENTITY, 'attu_id', [
        'type'         => 'varchar',
            'label'        => 'ATTU ID',
            'input'        => 'text',
            'required'     => false,
            'visible'      => true,
            'user_defined' => true,
            'position'     => 215,
            'system'       => 0,
            'unique' => true,
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
