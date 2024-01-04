<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Plugin\CatalogInventory\Model\Indexer\Stock;

use Amasty\ReportBuilder\Model\Indexer\Stock\Indexer as StockIndexer;
use Magento\Framework\Module\Manager as ModuleManager;

class ReindexStockData
{
    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @var StockIndexer
     */
    private $stockIndexer;

    public function __construct(ModuleManager $moduleManager, StockIndexer $stockIndexer)
    {
        $this->moduleManager = $moduleManager;
        $this->stockIndexer = $stockIndexer;
    }

    /**
     * If Magento_Inventory enabled,
     * we wait MSI indexer,
     * and reindex stock data after MSI indexer
     *
     * @see \Magento\CatalogInventory\Model\Indexer\Stock::executeFull
     *
     * @return void
     */
    public function afterExecuteFull(): void
    {
        if ($this->moduleManager->isEnabled('Magento_Inventory')) {
            $this->stockIndexer->execute();
        }
    }
}
