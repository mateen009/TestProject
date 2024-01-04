<?php
namespace Mobility\OutOfOfficeApprovals\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Config;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CustomerApprovalAttributes implements DataPatchInterface
{
	const ATTRIBUTES = [
		'ooo_enabled',
		'ooo_startdate',
		'ooo_enddate',
		'ooo_email'
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
        
        $customerSetup->addAttribute(Customer::ENTITY, 'ooo_enabled', [
            'type' 		   => 'int',
            'input' 	   => 'select',
            'label' 	   => 'Out of Office is Enabled?',
            'source' 	   => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
            'required'     => false,
			'visible'      => true,
			'user_defined' => true,
			'position'     => 213,
			'default' 	   => '0',
			'system'       => 0,
        ]);

        $customerSetup->addAttribute(Customer::ENTITY, 'ooo_startdate', [
				'type'         => 'datetime',
				'label'        => 'Out of Office Start Date',
				'input'        => 'date',
				'required'     => false,
				'visible'      => true,
				'user_defined' => true,
				'position'     => 214,
				'system'       => 0,
			]
		);

        $customerSetup->addAttribute(Customer::ENTITY, 'ooo_enddate', [
				'type'         => 'datetime',
				'label'        => 'Out of Office End Date',
				'input'        => 'date',
				'required'     => false,
				'visible'      => true,
				'user_defined' => true,
				'position'     => 215,
				'system'       => 0,
			]
		);

        $customerSetup->addAttribute(Customer::ENTITY, 'ooo_email', [
				'type'         => 'varchar',
				'label'        => 'Out of Office Alternate Email',
				'input'        => 'text',
				'required'     => false,
				'visible'      => true,
				'user_defined' => true,
				'position'     => 216,
				'system'       => 0,
			]
		);

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