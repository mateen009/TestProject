<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Indexer\Stock;

use Amasty\ReportBuilder\Model\Indexer\Stock\Table\Column\GetStaticColumns;
use Amasty\ReportBuilder\Model\Indexer\Stock\Table\CreateTable;
use Amasty\ReportBuilder\Model\Indexer\Stock\Table\GetTable;
use Amasty\ReportBuilder\Model\ResourceModel\Indexer\Stock\GetDefaultSelect;
use Amasty\ReportBuilder\Model\ResourceModel\Indexer\Stock\IndexResource;
use Amasty\ReportBuilder\Model\ResourceModel\Indexer\Stock\Strategy\StrategyInterface;
use Amasty\ReportBuilder\Model\ResourceModel\Indexer\Stock\TableWorker;
use Exception;
use Magento\Framework\DB\Query\BatchIteratorInterface;
use Magento\Framework\DB\Query\Generator as QueryGenerator;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Indexer
{
    /**
     * @var GetDefaultSelect
     */
    private $getDefaultSelect;

    /**
     * @var CreateTable
     */
    private $createTable;

    /**
     * @var QueryGenerator
     */
    private $queryGenerator;

    /**
     * @var TableWorker
     */
    private $tableWorker;

    /**
     * @var StrategyInterface[]
     */
    private $strategies;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var GetTable
     */
    private $getTable;

    /**
     * @var int
     */
    private $batchCount;

    public function __construct(
        GetDefaultSelect $getDefaultSelect,
        CreateTable $createTable,
        GetTable $getTable,
        QueryGenerator $queryGenerator,
        TableWorker $tableWorker,
        LoggerInterface $logger,
        array $strategies,
        int $batchCount = 10000
    ) {
        $this->getDefaultSelect = $getDefaultSelect;
        $this->createTable = $createTable;
        $this->queryGenerator = $queryGenerator;
        $this->tableWorker = $tableWorker;
        $this->logger = $logger;
        $this->strategies = $strategies;
        $this->getTable = $getTable;
        $this->batchCount = $batchCount;
    }

    public function execute(): void
    {
        try {
            $this->createTable->execute(IndexResource::MAIN_TABLE);
            $this->createTable->execute(IndexResource::REPLICA_TABLE, true);

            $this->populateTemporaryTable();

            $this->tableWorker->syncDataFull();
            $this->tableWorker->switchTables();
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Populate temp table with stock data.
     *
     * @return void
     * @throws LocalizedException
     */
    private function populateTemporaryTable(): void
    {
        $this->tableWorker->createTemporaryTable($this->getTable->execute($this->tableWorker->getIdxTable()));

        foreach ($this->strategies as $strategy) {
            $select = $this->getDefaultSelect->execute();
            $joinedColumns = [
                GetStaticColumns::SKU_COLUMN,
                GetStaticColumns::PRODUCT_ID_COLUMN
            ];

            $strategy->filter($select);
            foreach ($strategy->getSelectBuilders() as $selectBuilder) {
                array_push($joinedColumns, ...$selectBuilder->execute($select));
            }

            $batchQueries = $this->queryGenerator->generate(
                'entity_id',
                $select,
                $this->batchCount,
                BatchIteratorInterface::UNIQUE_FIELD_ITERATOR
            );

            foreach ($batchQueries as $batchQuery) {
                $this->tableWorker->insertToTemporaryTable($batchQuery, $joinedColumns);
            }
        }
    }
}
