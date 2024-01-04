<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme\Builder\Filter\Entity;

interface IsTableExistInterface
{
    /**
     * @param array $schemeData All scheme data
     * @param string $entityName for validate
     * @return bool
     */
    public function execute(array $schemeData, string $entityName): bool;
}
