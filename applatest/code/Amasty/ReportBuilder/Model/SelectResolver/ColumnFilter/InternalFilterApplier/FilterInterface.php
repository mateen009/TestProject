<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnFilter\InternalFilterApplier;

use Amasty\ReportBuilder\Api\ColumnInterface;

interface FilterInterface
{
    /**
     * @param ColumnInterface $column
     * @param array $conditions
     * @return bool
     */
    public function apply(ColumnInterface $column, array $conditions): bool;
}
