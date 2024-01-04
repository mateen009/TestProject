<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnFilter;

interface FilterResolverInterface
{
    /**
     * Get all filters as array
     *
     * @return array
     */
    public function resolve(): ?array;
}
