<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Indexer\Stock\Table\Column\Msi;

class GetQtyColumnAlias implements GetColumnAliasInterface
{
    public function execute(int $stockId): string
    {
        return sprintf('qty_stock_%d', $stockId);
    }
}
