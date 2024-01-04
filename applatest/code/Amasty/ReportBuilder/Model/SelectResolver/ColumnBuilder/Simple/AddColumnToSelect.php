<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnBuilder\Simple;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Model\ResourceModel\Report\Data\Select;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnBuilder\RelationHelper;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnExpressionResolverInterface;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolverInterface;
use Amasty\ReportBuilder\Model\SelectResolver\Context;
use Amasty\ReportBuilder\Model\SelectResolver\EntitySimpleRelationResolver;
use Amasty\ReportBuilder\Model\SelectResolver\RelationResolverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class AddColumnToSelect
{
    /**
     * @var EntitySimpleRelationResolver
     */
    private $simpleRelationResolver;

    /**
     * @var ColumnExpressionResolverInterface
     */
    private $columnExpressionResolver;

    /**
     * @var RelationResolverInterface
     */
    private $relationResolver;

    /**
     * @var RelationHelper
     */
    private $relationHelper;

    public function __construct(
        Context $context,
        RelationResolverInterface $relationResolver,
        RelationHelper $relationHelper
    ) {
        $this->simpleRelationResolver = $context->getSimpleRelationResolver();
        $this->columnExpressionResolver = $context->getColumnExpressionResolver();
        $this->relationResolver = $relationResolver;
        $this->relationHelper = $relationHelper;
    }

    /**
     * @param Select $select
     * @param ColumnInterface $columnToJoin column from entity which we joined
     * @param string $originalColumnId selected in report (can be different $columnToJoin, e.x. foreign)
     * @param array $selectColumnData include params needed for select (alias , etc.)
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(
        Select $select,
        ColumnInterface $columnToJoin,
        string $originalColumnId,
        array $selectColumnData
    ): void {
        $relations = $this->relationResolver->resolve();
        $simpleRelations = $this->simpleRelationResolver->resolve();

        if (!isset($relations[$columnToJoin->getEntityName()])) {
            return;
        }

        if (in_array($columnToJoin->getEntityName(), $simpleRelations)) {
            $expression = $selectColumnData[ColumnResolverInterface::EXPRESSION];
            $select->columns([$selectColumnData[ColumnResolverInterface::ALIAS] => $expression]);
        } elseif (isset($relations[$columnToJoin->getEntityName()])) {
            $relation = $relations[$columnToJoin->getEntityName()];
            $internalExpression = $selectColumnData[ColumnResolverInterface::EXPRESSION_INTERNAL];

            if ($relation[RelationResolverInterface::TABLE] instanceof Select) {
                $subSelect = $relation[RelationResolverInterface::TABLE];
                $subSelect->columns([$selectColumnData[ColumnResolverInterface::ALIAS] => $internalExpression]);
                $this->relationHelper->throwRelations($select, $selectColumnData, $relation);
            } else {
                $parentRelation = $this->relationHelper->getParentSubSelectRelation($relation, $relations);
                if ($parentRelation) {
                    $subSelect = $parentRelation[RelationResolverInterface::TABLE];
                    $subSelect->columns([$selectColumnData[ColumnResolverInterface::ALIAS] => $internalExpression]);
                    $this->relationHelper->throwRelations($select, $selectColumnData, $parentRelation);
                } else {
                    $select->columns([$selectColumnData[ColumnResolverInterface::ALIAS] => $internalExpression]);
                }
            }
        }
    }
}
