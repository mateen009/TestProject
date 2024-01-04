<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\ResourceModel\Indexer\Stock\Select;

use Magento\Framework\DB\Select;

interface BuilderInterface
{
    /**
     * Return array of joined columns.
     *
     * @param Select $select
     * @return array
     */
    public function execute(Select $select): array;
}
