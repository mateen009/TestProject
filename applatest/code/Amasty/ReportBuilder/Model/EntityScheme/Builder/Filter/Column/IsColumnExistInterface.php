<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme\Builder\Filter\Column;

use Amasty\ReportBuilder\Exception\NotExistColumnException;

interface IsColumnExistInterface
{
    /**
     * @param array $schemeData
     * @param string $entityName
     * @param string $columnName
     * @return bool
     */
    public function execute(array $schemeData, string $entityName, string $columnName): bool;
}
