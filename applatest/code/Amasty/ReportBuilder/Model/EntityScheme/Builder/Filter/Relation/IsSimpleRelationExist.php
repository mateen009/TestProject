<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme\Builder\Filter\Relation;

use Amasty\ReportBuilder\Api\EntityInterface;
use Amasty\ReportBuilder\Api\RelationInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Builder\Filter\Column\IsColumnExistInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Builder\Filter\Entity\IsTableExistInterface;

class IsSimpleRelationExist implements IsRelationExistInterface
{
    /**
     * @var IsTableExistInterface
     */
    private $isTableExist;

    /**
     * @var IsColumnExistInterface
     */
    private $isColumnExist;

    public function __construct(IsTableExistInterface $isTableExist, IsColumnExistInterface $isColumnExist)
    {
        $this->isTableExist = $isTableExist;
        $this->isColumnExist = $isColumnExist;
    }

    public function execute(array $schemeData, string $entityName, string $relationName): bool
    {
        $relation = $schemeData[$entityName][EntityInterface::RELATIONS][$relationName];
        return $this->isTableExist->execute($schemeData, $relation[RelationInterface::NAME])
            && $this->isColumnExist->execute(
                $schemeData,
                $relation[RelationInterface::NAME],
                $relation[RelationInterface::REFERENCE_COLUMN]
            );
    }
}
