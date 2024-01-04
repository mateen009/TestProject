<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnOrder;

use Amasty\ReportBuilder\Model\ResourceModel\Report\Data\Select;

interface OrderApplierInterface
{
    /**
     * Apply all orders to select
     *
     * @param Select $select
     */
    public function apply(Select $select): void;
}
