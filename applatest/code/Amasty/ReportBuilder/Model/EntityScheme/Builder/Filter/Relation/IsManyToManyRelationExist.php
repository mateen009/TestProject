<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme\Builder\Filter\Relation;

use Amasty\ReportBuilder\Api\EntityInterface;
use Amasty\ReportBuilder\Api\RelationInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Builder\Filter\Column\IsColumnExistInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Builder\Filter\Entity\IsTableExistInterface;
use Amasty\ReportBuilder\Model\ResourceModel\Table\IsColumnExist as IsColumnExistResource;
use Amasty\ReportBuilder\Model\ResourceModel\Table\IsTableExist as IsTableExistResource;

class IsManyToManyRelationExist implements IsRelationExistInterface
{
    /**
     * @var IsTableExistInterface
     */
    private $isTableExist;

    /**
     * @var IsColumnExistInterface
     */
    private $isColumnExist;

    /**
     * @var IsColumnExistResource
     */
    private $isColumnExistResource;

    /**
     * @var IsTableExistResource
     */
    private $isTableExistResource;

    public function __construct(
        IsTableExistInterface $isTableExist,
        IsColumnExistInterface $isColumnExist,
        IsTableExistResource $isTableExistResource,
        IsColumnExistResource $isColumnExistResource
    ) {
        $this->isTableExist = $isTableExist;
        $this->isColumnExist = $isColumnExist;
        $this->isColumnExistResource = $isColumnExistResource;
        $this->isTableExistResource = $isTableExistResource;
    }

    public function execute(array $schemeData, string $entityName, string $relationName): bool
    {
        $relation = $schemeData[$entityName][EntityInterface::RELATIONS][$relationName];
        return $this->isTableExist->execute($schemeData, $relation[RelationInterface::NAME])
            && $this->isColumnExist->execute(
                $schemeData,
                $relation[RelationInterface::NAME],
                $relation[RelationInterface::REFERENCE_COLUMN]
            )
            && $this->isTableExistResource->execute($relation[RelationInterface::RELATION_TABLE])
            && $this->isColumnExistResource->execute(
                $relation[RelationInterface::RELATION_TABLE],
                $relation[RelationInterface::RELATION_REFERENCE_COLUMN]
            )
            && $this->isColumnExistResource->execute(
                $relation[RelationInterface::RELATION_TABLE],
                $relation[RelationInterface::RELATION_COLUMN]
            );
    }
}
