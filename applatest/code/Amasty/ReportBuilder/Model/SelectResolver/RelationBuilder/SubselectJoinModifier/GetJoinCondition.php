<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\RelationBuilder\SubselectJoinModifier;

use Amasty\ReportBuilder\Api\RelationInterface;
use Amasty\ReportBuilder\Model\ReportResolver;
use Amasty\ReportBuilder\Model\SelectResolver\Context;
use Amasty\ReportBuilder\Model\SelectResolver\EntitySimpleRelationResolver;
use Amasty\ReportBuilder\Model\SelectResolver\RelationResolverInterface;

class GetJoinCondition
{
    /**
     * @var ReportResolver
     */
    private $reportResolver;

    /**
     * @var EntitySimpleRelationResolver
     */
    private $simpleRelationResolver;

    public function __construct(
        Context $context
    ) {
        $this->reportResolver = $context->getReportResolver();
        $this->simpleRelationResolver = $context->getSimpleRelationResolver();
    }

    /**
     * @param RelationInterface $relation
     * @param array $selectRelationData
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(RelationInterface $relation, array $selectRelationData): string
    {
        return sprintf(
            '`%s`.%s = `%s`.%s',
            $selectRelationData[RelationResolverInterface::PARENT],
            $relation->getReferenceColumn(),
            $selectRelationData[RelationResolverInterface::ALIAS],
            $relation->getRelationReferenceColumn() ?: $relation->getColumn()
        );
    }
}
