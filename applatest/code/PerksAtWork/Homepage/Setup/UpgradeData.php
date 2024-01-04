<?php
 namespace PerksAtWork\Homepage\Setup;

 use Magento\Eav\Setup\EavSetup;
 use Magento\Eav\Setup\EavSetupFactory;
 use Magento\Framework\Setup\UpgradeDataInterface;
 use Magento\Framework\Setup\ModuleContextInterface;
 use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface {
 private $eavSetupFactory;
 protected $logger;

public function __construct(EavSetupFactory $eavSetupFactory,\Psr\Log\LoggerInterface $logger) {
 $this->eavSetupFactory = $eavSetupFactory;
 $this->logger = $logger;
 }

public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
 
 
 $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
 $eavSetup->addAttribute(
 \Magento\Catalog\Model\Product::ENTITY,
 'homepage',[
 'type' => 'text',
 'backend' => '',
 'frontend' => '',
 'label' => 'Show on Homepage',
 'input' => 'select',
 'class' => '',
 'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
 'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
 'visible' => true,
 'required' => true,
 'user_defined' => false,
 'default' => '',
 'searchable' => false,
 'filterable' => false,
 'comparable' => false,
 'visible_on_front' => false,
 'used_in_product_listing' => true,
 'unique' => false,
 'apply_to' => ''
 ]
 );

 $eavSetup->addAttribute(
    \Magento\Catalog\Model\Product::ENTITY,
    'position_in_homepage',
    [
    'type' => 'text',
    'backend' => '',
    'frontend' => '',
    'label' => 'Position on Homepage',
    'input' => 'text',
    'class' => '',
    'source' => '',
    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
    'visible' => true,
    'required' => false,
    'user_defined' => false,
    'default' => '',
    'searchable' => false,
    'filterable' => false,
    'comparable' => false,
    'visible_on_front' => false,
    'used_in_product_listing' => true,
    'unique' => false,
    'apply_to' => ''
    ]
    );

    
 
 
 }
 
 }