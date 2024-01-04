<?php

namespace AscentDigital\PayStandCron\Setup;

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
        if (version_compare($context->getVersion(), '2.3.0', '<')) {
            $connection = $setup->getConnection();

            // first order column
            $connection->addColumn(
                $setup->getTable('sales_order'),
                'paystand_payment_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'default' => 0,
                    'comment' => 'Paystand Payment Id'
                ]
            );
            // 2nd order column
            $connection->addColumn(
                $setup->getTable('sales_order'),
                'paystand_payment_status',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 100,
                    'nullable' => true,
                    'default' => '',
                    'comment' => 'Paystand Payment Status'
                ]
            );
            $connection->addColumn(
                $setup->getTable('sales_order'),
                'paystand_nsid_status',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 100,
                    'nullable' => true,
                    'default' => '',
                    'comment' => 'Paystand NSID Status'
                ]
            );
        }
    }
}