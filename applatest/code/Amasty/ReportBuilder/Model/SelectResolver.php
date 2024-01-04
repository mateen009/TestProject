<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model;

use Amasty\ReportBuilder\Api\SelectResolverInterface;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnBuilderInterface;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnFilter\FilterApplierInterface;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnFilter\FilterModifierInterface;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnOrder\OrderApplierInterface;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnOrder\OrderModifierInterface;
use Amasty\ReportBuilder\Model\ResourceModel\Report\Data\Select;
use Amasty\ReportBuilder\Model\SelectResolver\MainTableBuilderInterface;
use Amasty\ReportBuilder\Model\SelectResolver\RelationBuilderInterface;

class SelectResolver implements SelectResolverInterface
{
    /**
     * @var RelationBuilderInterface
     */
    private $relationBuilder;

    /**
     * @var ColumnBuilderInterface
     */
    private $columnBuilder;

    /**
     * @var ReportResolver
     */
    private $reportResolver;

    /**
     * @var MainTableBuilderInterface
     */
    private $mainTableBuilder;

    /**
     * @var FilterModifierInterface
     */
    private $filterModifier;

    /**
     * @var FilterApplierInterface
     */
    private $filterApplier;

    /**
     * @var OrderModifierInterface
     */
    private $orderModifier;

    /**
     * @var OrderApplierInterface
     */
    private $orderApplier;

    /**
     * @var int
     */
    private $reportId = null;

    /**
     * @var string
     */
    private $interval = null;

    public function __construct(
        RelationBuilderInterface $relationBuilder,
        ColumnBuilderInterface $columnBuilder,
        ReportResolver $reportResolver,
        MainTableBuilderInterface $mainTableBuilder,
        FilterModifierInterface $filterModifier,
        FilterApplierInterface $filterApplier,
        OrderApplierInterface $orderApplier,
        OrderModifierInterface $orderModifier,
        $reportId = null
    ) {
        $this->relationBuilder = $relationBuilder;
        $this->columnBuilder = $columnBuilder;
        $this->reportResolver = $reportResolver;
        $this->mainTableBuilder = $mainTableBuilder;
        $this->filterModifier = $filterModifier;
        $this->filterApplier = $filterApplier;
        $this->orderApplier = $orderApplier;
        $this->orderModifier = $orderModifier;
        $this->reportId = $reportId;
    }

    /**
     * @return Select
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSelect(): Select
    {
        $this->reportResolver->resolve($this->reportId);

        $select = $this->mainTableBuilder->build($this->interval);
        $this->relationBuilder->build($select);
        $this->columnBuilder->build($select);

        return $select;
    }

    public function applyFilters(Select $select): void
    {
        $this->filterApplier->apply($select);
        $this->orderApplier->apply($select);
    }

    /**
     * @param int|null $reportId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setReportId(?int $reportId = null): void
    {
        $this->reportId = $reportId;
    }

    public function setInterval(string $interval): void
    {
        $this->interval = $interval;
    }

    public function addFilter(string $field, ?array $condition = null): void
    {
        $this->filterModifier->modify($field, $condition);
    }

    public function setOrder(string $field, string $direction): void
    {
        $this->orderModifier->modify($field, $direction);
    }
}
