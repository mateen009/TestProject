<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\RelationModifiers\TypeTableModifier;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Api\EntityScheme\SchemeInterface;
use Amasty\ReportBuilder\Api\RelationInterface;
use Amasty\ReportBuilder\Model\ResourceModel\Report\Data\Select;
use Amasty\ReportBuilder\Model\ResourceModel\Report\Data\SelectFactory;
use Magento\Framework\App\ResourceConnection;

class CreateSubselect
{
    /**
     * @var ResourceConnection
     */
    private $connectionResource;

    /**
     * @var SelectFactory
     */
    private $selectFactory;

    public function __construct(
        ResourceConnection $connectionResource,
        SelectFactory $selectFactory
    ) {
        $this->connectionResource = $connectionResource;
        $this->selectFactory = $selectFactory;
    }

    public function execute(
        SchemeInterface $entityScheme,
        RelationInterface $relationScheme,
        array $relation
    ): Select {
        $relatedEntity = $entityScheme->getEntityByName($relation[ReportInterface::SCHEME_ENTITY]);

        $select = $this->selectFactory->create();
        $select->from([
            'relation_table' => $this->connectionResource->getTableName($relationScheme->getRelationTable())
        ])->joinInner(
            [$relatedEntity->getName() => $this->connectionResource->getTableName($relatedEntity->getMainTable())],
            sprintf(
                '`%s`.%s = relation_table.%s',
                $relatedEntity->getName(),
                $relationScheme->getReferenceColumn(),
                $relationScheme->getRelationReferenceColumn()
            )
        );

        foreach ($relatedEntity->getExpressions() as $expression) {
            $select->where($expression);
        }

        return $select;
    }
}
