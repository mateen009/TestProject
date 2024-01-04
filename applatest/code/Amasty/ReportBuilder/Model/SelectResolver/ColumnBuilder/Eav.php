<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnBuilder;

use Amasty\ReportBuilder\Model\ResourceModel\Report\Data\Select;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnBuilder\Eav\SubSelectRelationBuilder;
use Amasty\ReportBuilder\Model\SelectResolver\Context;
use Amasty\ReportBuilder\Model\SelectResolver\RelationBuilderInterface;
use Amasty\ReportBuilder\Model\SelectResolver\RelationResolverInterface;

class Eav implements BuilderInterface
{
    /**
     * @var \Amasty\ReportBuilder\Model\EntityScheme\Provider
     */
    private $schemeProvider;

    /**
     * @var RelationResolverInterface
     */
    private $relationResolver;

    /**
     * @var SubSelectRelationBuilder
     */
    private $subSelectRelationBuilder;

    /**
     * @var RelationHelper
     */
    private $relationHelper;

    public function __construct(
        Context $context,
        RelationResolverInterface $relationResolver,
        SubSelectRelationBuilder $subSelectRelationBuilder,
        RelationHelper $relationHelper
    ) {
        $this->schemeProvider = $context->getEntitySchemeProvider();
        $this->relationResolver = $relationResolver;
        $this->subSelectRelationBuilder = $subSelectRelationBuilder;
        $this->relationHelper = $relationHelper;
    }

    public function build(Select $select, string $columnId, array $columnData): void
    {
        $scheme = $this->schemeProvider->getEntityScheme();
        $relations = $this->relationResolver->resolve();

        $column = $scheme->getColumnById($columnId);

        $relation = [];
        if (isset($relations[$column->getEntityName()])) {
            $relation = $relations[$column->getEntityName()];
            $subSelect = $relation[RelationResolverInterface::TABLE];
            if (!$subSelect instanceof Select) {
                $relation = $this->relationHelper->getParentSubSelectRelation($relation, $relations);
                if ($relation) {
                    $subSelect = $relation[RelationResolverInterface::TABLE];
                } else {
                    $subSelect = $select;
                }
            }
        } else {
            $subSelect = $select;
        }

        $this->joinColumn($subSelect, $columnId, $columnData);

        if ($relation) {
            $this->relationHelper->throwRelations($select, $columnData, $relation);
        }
    }

    private function joinColumn(Select $select, string $columnId, array $columnData): void
    {
        $relation = $this->subSelectRelationBuilder->build($columnId, $columnData);
        $select->joinByType(
            $relation[RelationBuilderInterface::TYPE],
            [$relation[RelationBuilderInterface::ALIAS] => $relation[RelationBuilderInterface::TABLE]],
            $relation[RelationBuilderInterface::CONDITION],
            $relation[RelationBuilderInterface::COLUMNS]
        );
    }
}
