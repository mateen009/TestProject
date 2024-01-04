<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Cron\Stock;

use Amasty\ReportBuilder\Model\Indexer\Stock\Indexer as StockIndexer;

class Indexer
{
    /**
     * @var StockIndexer
     */
    private $stockIndexer;

    public function __construct(StockIndexer $stockIndexer)
    {
        $this->stockIndexer = $stockIndexer;
    }

    public function execute(): void
    {
        $this->stockIndexer->execute();
    }
}
