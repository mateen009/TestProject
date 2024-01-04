<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Indexer\Stock\Table;

class GetMsiTableName
{
    /**
     * Get inventory stock index table name.

     * @param int $stockId
     * @return string
     */
    public function execute(int $stockId): string
    {
        return sprintf('inventory_stock_%d', $stockId);
    }
}
