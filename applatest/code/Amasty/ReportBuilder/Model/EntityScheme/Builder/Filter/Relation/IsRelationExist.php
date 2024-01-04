<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme\Builder\Filter\Relation;

use Amasty\ReportBuilder\Api\EntityInterface;
use Amasty\ReportBuilder\Api\RelationInterface;

class IsRelationExist implements IsRelationExistInterface
{
    /**
     * @var IsRelationExistInterface
     */
    private $isRelationExistDefault;

    /**
     * @var IsRelationExistInterface[]
     */
    private $isRelationExistPool;

    public function __construct(
        IsRelationExistInterface $isRelationExistDefault,
        array $isRelationExistPool = []
    ) {
        $this->isRelationExistDefault = $isRelationExistDefault;
        $this->isRelationExistPool = $isRelationExistPool;
    }

    public function execute(array $schemeData, string $entityName, string $relationName): bool
    {
        $relationType = $schemeData[$entityName][EntityInterface::RELATIONS][$relationName]
            [RelationInterface::RELATIONSHIP_TYPE];
        $isRelationExist = $this->isRelationExistPool[$relationType] ?? $this->isRelationExistDefault;
        return $isRelationExist->execute($schemeData, $entityName, $relationName);
    }
}
