<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme\Builder\Filter\Relation;

interface IsRelationExistInterface
{
    /**
     * @param array $schemeData All scheme data
     * @param string $entityName
     * @param string $relationName
     * @return bool
     */
    public function execute(array $schemeData, string $entityName, string $relationName): bool;
}
