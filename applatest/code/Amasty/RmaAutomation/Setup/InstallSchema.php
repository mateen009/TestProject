<?php

namespace Amasty\RmaAutomation\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var Operation\CreateAutomationRuleTable
     */
    private $createAutomationRuleTable;

    /**
     * @var Operation\CreateRuleActionTable
     */
    private $createRuleActionTable;

    public function __construct(
        Operation\CreateAutomationRuleTable $createAutomationRuleTable,
        Operation\CreateRuleActionTable $createRuleActionTable
    ) {
        $this->createAutomationRuleTable = $createAutomationRuleTable;
        $this->createRuleActionTable = $createRuleActionTable;
    }

    /**
     * @inheritdoc
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->createAutomationRuleTable->execute($setup);
        $this->createRuleActionTable->execute($setup);

        $setup->endSetup();
    }
}
