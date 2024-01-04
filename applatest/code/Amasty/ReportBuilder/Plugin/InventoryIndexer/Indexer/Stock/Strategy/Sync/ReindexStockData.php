<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Plugin\InventoryIndexer\Indexer\Stock\Strategy\Sync;

use Amasty\ReportBuilder\Model\Indexer\Stock\GetStocks;
use Amasty\ReportBuilder\Model\Indexer\Stock\Indexer as StockIndexer;
use Magento\InventoryIndexer\Indexer\Stock\Strategy\Sync as SyncStrategy;

class ReindexStockData
{
    /**
     * @var GetStocks
     */
    private $getStocks;

    /**
     * @var StockIndexer
     */
    private $stockIndexer;

    public function __construct(GetStocks $getStocks, StockIndexer $stockIndexer)
    {
        $this->getStocks = $getStocks;
        $this->stockIndexer = $stockIndexer;
    }

    /**
     * Use executeList instead of executeFull , because in async strategy in executeFull,
     * Magento use publisher, which run executeList method of sync strategy.
     * For detect executeFull in executeList compate count stocks.
     * Around plugin needed for correct order, because magento reindex composite with plugins.
     *
     * @param SyncStrategy $subject
     * @param callable $proceed
     * @param int[] $stockIds
     * @return void
     *
     * @see \Magento\InventoryIndexer\Indexer\Stock\Strategy\Async::executeFull
     * @see \Magento\InventoryIndexer\Indexer\Stock\Strategy\Sync::executeList
     *
     */
    public function aroundExecuteList(SyncStrategy $subject, callable $proceed, array $stockIds): void
    {
        $proceed($stockIds);
        $allStockCount = count($this->getStocks->execute()) + 1; // 1 - is default stock
        if ($allStockCount === count($stockIds)) {
            $this->stockIndexer->execute();
        }
    }
}
