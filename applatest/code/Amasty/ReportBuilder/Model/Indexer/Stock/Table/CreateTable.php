<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Indexer\Stock\Table;

use Amasty\ReportBuilder\Model\ResourceModel\Indexer\Stock\IndexResource;
use Zend_Db_Exception;

class CreateTable
{
    /**
     * @var IndexResource
     */
    private $indexResource;

    /**
     * @var GetTable
     */
    private $getTable;

    public function __construct(IndexResource $indexResource, GetTable $getTable)
    {
        $this->indexResource = $indexResource;
        $this->getTable = $getTable;
    }

    /**
     * @param string $tableName
     * @param bool $force
     * @return void
     * @throws Zend_Db_Exception
     */
    public function execute(string $tableName, bool $force = false): void
    {
        if ($this->indexResource->isTableExists($tableName) && !$force) {
            return;
        }

        $this->indexResource->dropTable($tableName);
        $table = $this->getTable->execute($tableName);
        $this->indexResource->createTable($table);
    }
}
