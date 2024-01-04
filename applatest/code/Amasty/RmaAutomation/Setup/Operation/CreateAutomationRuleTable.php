<?php

namespace Amasty\RmaAutomation\Setup\Operation;

use Amasty\RmaAutomation\Api\Data\AutomationRuleInterface;
use Amasty\RmaAutomation\Model\AutomationRule\ResourceModel\AutomationRule;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class CreateAutomationRuleTable
 */
class CreateAutomationRuleTable
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
        $mainTable = $setup->getTable(AutomationRule::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $mainTable
            )->setComment(
                'Amasty Rma Automation Rules Table'
            )->addColumn(
                AutomationRuleInterface::RULE_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ],
                'ID'
            )->addColumn(
                AutomationRuleInterface::NAME,
                Table::TYPE_TEXT,
                225,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Automation Rule Name'
            )->addColumn(
                AutomationRuleInterface::STATUS,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false
                ],
                'Status'
            )->addColumn(
                AutomationRuleInterface::PRIORITY,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Priority of rule'
            )->addColumn(
                AutomationRuleInterface::FURTHER_PROCESSING,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false
                ],
                'Is Further Processing Enable'
            )->addColumn(
                AutomationRuleInterface::APPLY_FOR_NEW,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false
                ],
                'Is Apply Rule For New Requests'
            )->addColumn(
                AutomationRuleInterface::APPLY_FOR_EXISTING,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false
                ],
                'Is Apply Rule For Existing Requests'
            )->addColumn(
                AutomationRuleInterface::CONDITIONS_SERIALIZED,
                Table::TYPE_TEXT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Serialized Conditions'
            );
    }
}
