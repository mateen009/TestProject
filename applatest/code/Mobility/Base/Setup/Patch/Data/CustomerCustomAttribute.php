<?php
namespace Mobility\Base\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Config;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CustomerCustomAttribute implements DataPatchInterface
{
	const ATTRIBUTES = [
		'Customer_Type',
		'SalesRep_ID',
		'OrderID',
		'ShipTo_Address_ID',
		'PriceList_ID',
		'SalesManager_ID',
		'TerritoryManager_ID',
		'Executive_ID',
		'Category_IDs',
		'Approval_1_ID',
		'Approval_2_ID',
		'customer_approval'
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

        $customerSetup->addAttribute(Customer::ENTITY, 'Customer_Type', [
            'type' 		   => 'int',
            'input' 	   => 'select',
            'label' 	   => 'Customer_Type',
            'source'       => 'Mobility\Base\Model\Config\Source\CustomerType',
            'required'     => false,
			'visible'      => true,
			'user_defined' => true,
			'position'     => 202,
			'system'       => 0,
        ]);

        $customerSetup->addAttribute(Customer::ENTITY, 'Category_IDs', [
            'type' 		   => 'text',
            'input' 	   => 'multiselect',
            'label' 	   => 'Category_IDs',
            'source' 	   => 'Mobility\Base\Model\Config\Source\Category',
            'required'     => false,
			'visible'      => true,
			'user_defined' => true,
			'position'     => 203,
			'system'       => 0,
        ]);

        $customerSetup->addAttribute(Customer::ENTITY, 'SalesRep_ID', [
				'type'         => 'varchar',
				'label'        => 'SalesRep_ID',
				'input'        => 'text',
				'required'     => false,
				'visible'      => true,
				'user_defined' => true,
				'position'     => 204,
				'system'       => 0,
			]
		);

		$customerSetup->addAttribute(Customer::ENTITY, 'OrderID', [
				'type'         => 'varchar',
				'label'        => 'OrderID',
				'input'        => 'text',
				'required'     => false,
				'visible'      => true,
				'user_defined' => true,
				'position'     => 205,
				'system'       => 0,
			]
		);

		$customerSetup->addAttribute(Customer::ENTITY, 'ShipTo_Address_ID', [
				'type'         => 'varchar',
				'label'        => 'ShipTo_Address_ID',
				'input'        => 'text',
				'required'     => false,
				'visible'      => true,
				'user_defined' => true,
				'position'     => 206,
				'system'       => 0,
			]
		);

		$customerSetup->addAttribute(Customer::ENTITY, 'PriceList_ID', [
				'type'         => 'varchar',
				'label'        => 'PriceList_ID',
				'input'        => 'text',
				'required'     => false,
				'visible'      => true,
				'user_defined' => true,
				'position'     => 207,
				'system'       => 0,
			]
		);

		$customerSetup->addAttribute(Customer::ENTITY, 'SalesManager_ID', [
				'type'         => 'varchar',
				'label'        => 'SalesManager_ID',
				'input'        => 'text',
				'required'     => false,
				'visible'      => true,
				'user_defined' => true,
				'position'     => 208,
				'system'       => 0,
			]
		);

		$customerSetup->addAttribute(Customer::ENTITY, 'TerritoryManager_ID', [
				'type'         => 'varchar',
				'label'        => 'TerritoryManager_ID',
				'input'        => 'text',
				'required'     => false,
				'visible'      => true,
				'user_defined' => true,
				'position'     => 209,
				'system'       => 0,
			]
		);

		$customerSetup->addAttribute(Customer::ENTITY, 'Executive_ID', [
				'type'         => 'varchar',
				'label'        => 'Executive_ID',
				'input'        => 'text',
				'required'     => false,
				'visible'      => true,
				'user_defined' => true,
				'position'     => 210,
				'system'       => 0,
			]
		);

		$customerSetup->addAttribute(Customer::ENTITY, 'Approval_1_ID', [
            'type' 		   => 'int',
            'input' 	   => 'select',
            'label' 	   => 'Approval-1_ID',
            'source' 	   => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
            'required'     => false,
			'visible'      => true,
			'user_defined' => true,
			'position'     => 211,
			'default' 	   => '0',
			'system'       => 0,
        ]);

        $customerSetup->addAttribute(Customer::ENTITY, 'Approval_2_ID', [
            'type' 		   => 'int',
            'input' 	   => 'select',
            'label' 	   => 'Approval-2_ID',
            'source' 	   => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
            'required'     => false,
			'visible'      => true,
			'user_defined' => true,
			'position'     => 212,
			'default' 	   => '0',
			'system'       => 0,
        ]);

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