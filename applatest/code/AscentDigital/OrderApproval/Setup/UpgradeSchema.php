<?php

namespace AscentDigital\OrderApproval\Setup;

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
        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $connection = $setup->getConnection();
            $connection->addColumn(
                $setup->getTable('sales_order_item'),
                'rma_return_status',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 30,
                    'nullable' => true,
                    'default' => 'No',
                    'comment' => 'RMA Return Status'
                ]
            );
            // //Order table
            // $setup->getConnection()
            //     ->addColumn(
            //         $setup->getTable('sales_order'),
            //         'dropdownatt',
            //         [
            //             'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            //             'length' => 255,
            //             'comment' => 'DD'
            //         ]
            //     );
        }
    }
}
