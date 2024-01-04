<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\ResourceModel\Indexer\Stock;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Zend_Db_Exception;

class IndexResource
{
    const MAIN_TABLE = 'amasty_report_builder_stock_index';
    const REPLICA_TABLE = 'amasty_report_builder_stock_index_replica';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param string $tableName
     * @return Table
     */
    public function getNewTable(string $tableName): Table
    {
        return $this->getConnection()->newTable($this->getTableName($tableName));
    }

    /**
     * @param Table $table
     * @return void
     * @throws Zend_Db_Exception
     */
    public function createTable(Table $table): void
    {
        $this->getConnection()->createTable($table);
    }

    /**
     * @param string $tableName
     * @return void
     */
    public function dropTable(string $tableName): void
    {
        $this->getConnection()->dropTable($this->getTableName($tableName));
    }

    /**
     * @param string $tableName
     * @return bool
     */
    public function isTableExists(string $tableName): bool
    {
        return $this->getConnection()->isTableExists($this->getTableName($tableName));
    }

    /**
     * @return AdapterInterface
     */
    public function getConnection(): AdapterInterface
    {
        return $this->resourceConnection->getConnection();
    }

    /**
     * @param string $tableName
     * @return string
     */
    public function getTableName(string $tableName): string
    {
        return $this->resourceConnection->getTableName($tableName);
    }
}
