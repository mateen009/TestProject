<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\RelationBuilder\SubselectJoinModifier;

use Amasty\ReportBuilder\Api\RelationInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Provider;
use Amasty\ReportBuilder\Model\ResourceModel\Report\Data\Select;
use Amasty\ReportBuilder\Model\SelectResolver\RelationResolverInterface;

class CreateSubselect
{
    /**
     * @var Provider
     */
    private $provider;

    public function __construct(
        Provider $provider
    ) {
        $this->provider = $provider;
    }

    public function execute(RelationInterface $relation, array $selectRelationData): Select
    {
        $relationEntityName = $selectRelationData[RelationResolverInterface::ALIAS];

        $select = $selectRelationData[RelationResolverInterface::EXPRESSION];
        $select->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns($this->getRelationColumns($relation, $relationEntityName))
            ->group($relation->getRelationReferenceColumn() ?? $relation->getColumn());

        return $select;
    }

    private function getRelationColumns(RelationInterface $relationScheme, string $relationEntityName): array
    {
        $relationColumnId = sprintf('%s.%s', $relationEntityName, $relationScheme->getColumn());
        $columns = [$relationColumnId];
        if ($relationScheme->getRelationReferenceColumn()) {
            $columns[] = $relationScheme->getRelationReferenceColumn();
        }

        return $columns;
    }
}
