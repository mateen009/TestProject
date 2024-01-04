<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Indexer\Stock;

use Amasty\ReportBuilder\Model\ResourceModel\LoadStocks;

class GetStocks
{
    const DEFAULT_STOCK_ID = 1;

    /**
     * @var LoadStocks
     */
    private $loadStocks;

    /**
     * @var array|null
     */
    private $stocks;

    public function __construct(LoadStocks $loadStocks)
    {
        $this->loadStocks = $loadStocks;
    }

    /**
     * Returned all stocks except default.
     * @return array
     */
    public function execute(): array
    {
        if ($this->stocks === null) {
            $allStocks = $this->loadStocks->execute();
            unset($allStocks[self::DEFAULT_STOCK_ID]);
            $this->stocks = $allStocks;
        }

        return $this->stocks;
    }
}
