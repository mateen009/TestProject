<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver;

use Amasty\ReportBuilder\Model\EntityScheme\Provider;
use Amasty\ReportBuilder\Model\ReportResolver;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnAggregationTypeResolver;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnModifiers\ColumnModifierInterface;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnModifiers\ExpressionModifier;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnStorageInterface;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnValidatorInterface;

class ColumnResolver implements ColumnResolverInterface
{
    /**
     * @var ColumnAggregationTypeResolver
     */
    private $columnAggregationTypeResolver;

    /**
     * @var ColumnStorageInterface
     */
    private $storage;

    /**
     * @var ReportResolver
     */
    private $reportResolver;

    /**
     * @var Provider
     */
    private $provider;

    /**
     * @var ColumnModifierInterface[]
     */
    private $pool;

    /**
     * @var ColumnValidatorInterface
     */
    private $columnValidator;

    /**
     * @var EntitySimpleRelationResolver
     */
    private $simpleRelationResolver;

    /**
     * @var ExpressionModifier
     */
    private $expressionModifier;

    public function __construct(
        ColumnAggregationTypeResolver $columnAggregationTypeResolver,
        ColumnStorageInterface $columnStorage,
        ReportResolver $reportResolver,
        Provider $provider,
        ColumnValidatorInterface $columnValidator,
        EntitySimpleRelationResolver $simpleRelationResolver,
        ExpressionModifier $expressionModifier,
        array $pool = []
    ) {
        $this->pool = $pool;
        $this->columnAggregationTypeResolver = $columnAggregationTypeResolver;
        $this->storage = $columnStorage;
        $this->reportResolver = $reportResolver;
        $this->provider = $provider;
        $this->columnValidator = $columnValidator;
        $this->simpleRelationResolver = $simpleRelationResolver;
        $this->expressionModifier = $expressionModifier;
    }

    public function resolve(): array
    {
        $columns = $this->storage->getAllColumns();
        if (empty($columns)) {
            $simpleRelations = $this->simpleRelationResolver->resolve();
            $columns = $this->reportResolver->resolve()->getAllColumns();

            $this->columnValidator->execute($columns);
            foreach ($columns as $columnId => $columnData) {
                $column = $this->provider->getEntityScheme()->getColumnById($columnId);
                $columnModifier = $this->pool[$column->getColumnType()];
                $columns[$columnId] = $columnModifier->modify($columnId, $columnData);
                if (!in_array($columns[$columnId][ColumnResolverInterface::ENTITY_NAME], $simpleRelations)) {
                    $columns[$columnId][ColumnResolverInterface::EXPRESSION]
                        = $columns[$columnId][ColumnResolverInterface::ALIAS];
                }
                $columns[$columnId] += $this->columnAggregationTypeResolver->build($columnId, $columnData);
                $columns[$columnId] = $this->expressionModifier->modify($columnId, $columns[$columnId])
                    + $columns[$columnId];
            }
            $this->storage->setColumns($columns);
        }

        return $columns;
    }
}
