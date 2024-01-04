<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\ResourceModel\Indexer\Stock\Strategy;

use Amasty\ReportBuilder\Model\ResourceModel\Indexer\Stock\Select\BuilderInterface;
use Magento\Framework\DB\Select;

interface StrategyInterface
{
    /**
     * @param Select $select
     * @return void
     */
    public function filter(Select $select): void;

    /**
     * @return BuilderInterface[]
     */
    public function getSelectBuilders(): array;
}
