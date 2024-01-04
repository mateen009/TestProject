<?php
namespace Cminds\Oapm\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    const TABLE_NAME = 'cminds_oapm_order';
    const PRIMARY_KEY = 'entity_id';

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $table = $setup->getConnection()->newTable(
            $setup->getTable(self::TABLE_NAME)
        )->addColumn(
            self::PRIMARY_KEY,
            Table::TYPE_INTEGER,
            null,
            [
                'auto_increment' => true,
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
            'Entity ID'
        )->addColumn(
            'order_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Order ID'
        )->addColumn(
            'hash',
            Table::TYPE_TEXT,
            32,
            ['nullable' => false],
            'Order hash'
        )->addColumn(
            'status',
            Table::TYPE_SMALLINT,
            1,
            ['unsigned' => true, 'nullable' => false],
            'Status'
        )->addColumn(
            'updated_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false],
            'Last updated date in UTC'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false],
            'Created date in UTC'
        )->addIndex(
            $setup->getIdxName(
                $setup->getTable(self::TABLE_NAME),
                'order_id',
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            'order_id',
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $setup->getIdxName($setup->getTable(self::TABLE_NAME), 'hash'),
            'hash'
        )->setComment('Cminds OAPM orders');

        $setup->getConnection()->createTable($table);
    }
}
