<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Indexer\Stock\Table\Column\Msi;

interface GetColumnAliasInterface
{
    /**
     * @param int $stockId
     * @return string
     */
    public function execute(int $stockId): string;
}
