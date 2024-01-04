<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\ResourceModel\Indexer\Stock;

use Exception;
use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Indexer\Table\Strategy as IndexerTableStrategy;
use Zend_Db_Exception;

class TableWorker
{
    /**
     * @var IndexResource
     */
    private $indexResource;

    /**
     * @var IndexerTableStrategy
     */
    private $indexerTableStrategy;

    /**
     * @var ActiveTableSwitcher
     */
    private $activeTableSwitcher;

    public function __construct(
        IndexResource $indexResource,
        IndexerTableStrategy $indexerTableStrategy,
        ActiveTableSwitcher $activeTableSwitcher
    ) {
        $this->indexResource = $indexResource;
        $this->indexerTableStrategy = $indexerTableStrategy;
        $this->activeTableSwitcher = $activeTableSwitcher;
    }

    private function getConnection(): AdapterInterface
    {
        return $this->indexResource->getConnection();
    }

    /**
     * @return string
     */
    public function getIdxTable(): string
    {
        $this->indexerTableStrategy->setUseIdxTable(true);
        return $this->indexerTableStrategy->prepareTableName(IndexResource::REPLICA_TABLE);
    }

    /**
     * @param Table $table
     * @return void
     * @throws Zend_Db_Exception
     */
    public function createTemporaryTable(Table $table): void
    {
        $this->getConnection()->createTemporaryTable($table);
    }

    /**
     * Populate temp table from select.
     *
     * @param Select $select
     * @param array $columns
     * @return void
     */
    public function insertToTemporaryTable(Select $select, array $columns): void
    {
        $query = $select->insertFromSelect(
            $this->indexResource->getTableName($this->getIdxTable()),
            $columns
        );
        $this->getConnection()->query($query);
    }

    /**
     * Move data from temp table to replica table.
     *
     * @return void
     * @throws Exception
     */
    public function syncDataFull(): void
    {
        $this->syncData($this->indexResource->getTableName(IndexResource::REPLICA_TABLE));
    }

    /**
     * Move data from temp table to passed table.
     *
     * @param string $destinationTable
     * @throws Exception
     */
    private function syncData(string $destinationTable): void
    {
        $this->getConnection()->beginTransaction();
        try {
            $this->getConnection()->delete($destinationTable);
            $this->insertFromTable(
                $this->indexResource->getTableName($this->getIdxTable()),
                $destinationTable
            );
            $this->getConnection()->commit();
        } catch (Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }
    }

    /**
     * Switch replica table with main table.
     *
     * @return void
     */
    public function switchTables(): void
    {
        $this->activeTableSwitcher->switchTable(
            $this->getConnection(),
            [$this->indexResource->getTableName(IndexResource::MAIN_TABLE)]
        );
    }

    private function insertFromTable(string $sourceTable, string $destTable): void
    {
        $sourceColumns = array_keys($this->getConnection()->describeTable($sourceTable));
        $targetColumns = array_keys($this->getConnection()->describeTable($destTable));

        $select = $this->getConnection()->select()->from($sourceTable, $sourceColumns);

        $this->getConnection()->query($this->getConnection()->insertFromSelect($select, $destTable, $targetColumns));
    }
}
