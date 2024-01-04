<?php

namespace Amasty\RmaAutomation\Setup\Operation;

use Amasty\RmaAutomation\Api\Data\AutomationRuleInterface;
use Amasty\RmaAutomation\Api\Data\RuleActionInterface;
use Amasty\RmaAutomation\Model\AutomationRule\ResourceModel\AutomationRule;
use Amasty\RmaAutomation\Model\AutomationRule\ResourceModel\RuleAction;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class CreateAutomationActionTable
 */
class CreateRuleActionTable
{
    /**
     * @param SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->createTable(
            $this->createTable($setup)
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @return Table
     * @throws \Zend_Db_Exception
     */
    private function createTable(SchemaSetupInterface $setup)
    {
        $mainTable = $setup->getTable(RuleAction::TABLE_NAME);
        $ruleTable = $setup->getTable(AutomationRule::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $mainTable
            )->setComment(
                'Amasty Rma Automation Rules Actions Table'
            )->addColumn(
                RuleActionInterface::ACTION_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ],
                'Action ID'
            )->addColumn(
                RuleActionInterface::RULE_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Rule ID'
            )->addColumn(
                RuleActionInterface::TYPE,
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => false
                ],
                'Rule Type'
            )->addColumn(
                RuleActionInterface::VALUE,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Action Value'
            )->addColumn(
                RuleActionInterface::ADDITIONAL_DATA,
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => false
                ],
                'Additional Data'
            )->addForeignKey(
                $setup->getFkName(
                    $mainTable,
                    RuleActionInterface::RULE_ID,
                    $ruleTable,
                    AutomationRuleInterface::RULE_ID
                ),
                RuleActionInterface::RULE_ID,
                $ruleTable,
                AutomationRuleInterface::RULE_ID,
                Table::ACTION_CASCADE
            );
    }
}
