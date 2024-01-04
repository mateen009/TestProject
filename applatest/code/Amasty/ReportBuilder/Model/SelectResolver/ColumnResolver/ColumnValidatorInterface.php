<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver;

interface ColumnValidatorInterface
{
    /**
     * @param array $columns
     * @return void
     */
    public function execute(array $columns): void;
}
