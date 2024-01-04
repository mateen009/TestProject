<?php

namespace Mobility\QuoteRequest\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if(version_compare($context->getVersion(), '2.3.0', '<')) {
            $connection = $setup->getConnection();
            $connection->addColumn(
                $setup->getTable('quote'),
                'attachment',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 300,
                    'nullable' => true,
                    'default' => '',
                    'comment' => 'Attachment PDF'
                ]
            );
            // 2nd quote column
            $connection->addColumn(
                $setup->getTable('quote'),
                'form_quote_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 255,
                    'nullable' => true,
                    'default' => 0,
                    'comment' => 'Form Quote Id'
                ]
            );
            // first order column
            $connection->addColumn(
                $setup->getTable('sales_order'),
                'form_quote_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 255,
                    'nullable' => true,
                    'default' => 0,
                    'comment' => 'Form Quote Id'
                ]
            );
            // 2nd order column
            $connection->addColumn(
                $setup->getTable('sales_order'),
                'attachment',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 300,
                    'nullable' => true,
                    'default' => '',
                    'comment' => 'Attachment PDF'
                ]
            );
        }
    }
}