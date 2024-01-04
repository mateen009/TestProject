<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnOrder;

use Amasty\ReportBuilder\Model\ResourceModel\Report;
use Amasty\ReportBuilder\Model\ResourceModel\Report\Data\Select;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnExpressionResolverInterface;

class OrderApplier implements OrderApplierInterface
{
    /**
     * @var OrderStorageInterface
     */
    private $orderStorage;

    /**
     * @var ColumnExpressionResolverInterface
     */
    private $columnExpressionResolver;

    /**
     * @var Report
     */
    private $reportResource;

    public function __construct(
        OrderStorageInterface $orderStorage,
        ColumnExpressionResolverInterface $columnExpressionResolver,
        Report $reportResource
    ) {
        $this->orderStorage = $orderStorage;
        $this->reportResource = $reportResource;
        $this->columnExpressionResolver = $columnExpressionResolver;
    }

    public function apply(Select $select): void
    {
        foreach ($this->orderStorage->getAllOrders() as $column => $direction) {
            $expression = $this->columnExpressionResolver->resolve($column);
            $select->order(sprintf('%s %s', $expression, $direction));
        }
    }
}
