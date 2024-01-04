<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnValidator;

use Amasty\ReportBuilder\Exception\NotExistColumnException;

interface IsColumnValidInterface
{
    /**
     * @param string $columnId
     * @return void
     * @throws NotExistColumnException
     */
    public function execute(string $columnId): void;
}
