<?php

namespace Cminds\Oapm\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class UpgradeSchema
 *
 * @package Cminds\Oapm\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     *
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.6', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('customer_group'),
                'group_manager_email',
                [
                    'type' => Table::TYPE_TEXT,
                    'unsigned' => true,
                    'nullable' => true,
                    'comment' => 'Group Manager Email',
                    'default' => ""
                ]
            );
        }

        $setup->endSetup();
    }
}
