<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\RelationBuilder;

use Amasty\ReportBuilder\Model\EntityScheme\Provider;
use Amasty\ReportBuilder\Model\ResourceModel\Report\Data\Select;
use Amasty\ReportBuilder\Model\SelectResolver\RelationBuilder\SubselectJoinModifier\CreateSubselect;
use Amasty\ReportBuilder\Model\SelectResolver\RelationBuilder\SubselectJoinModifier\GetJoinCondition;
use Amasty\ReportBuilder\Model\SelectResolver\RelationBuilderInterface;
use Amasty\ReportBuilder\Model\SelectResolver\RelationResolverInterface;

class SubselectJoinModifier implements JoinModifierInterface
{
    /**
     * @var Provider
     */
    private $provider;

    /**
     * @var CreateSubselect
     */
    private $createSubselect;

    /**
     * @var GetJoinCondition
     */
    private $getJoinCondition;

    public function __construct(
        Provider $provider,
        CreateSubselect $createSubselect,
        GetJoinCondition $getJoinCondition
    ) {
        $this->provider = $provider;
        $this->createSubselect = $createSubselect;
        $this->getJoinCondition = $getJoinCondition;
    }

    public function modify(array $selectRelations): array
    {
        foreach ($selectRelations as $relationData) {
            if ($this->isValid($relationData)) {
                $scheme = $this->provider->getEntityScheme();
                $entity = $scheme->getEntityByName($relationData[RelationResolverInterface::ALIAS]);
                $relation = $entity->getRelation($relationData[RelationResolverInterface::PARENT]);

                $selectRelations[$relationData[RelationResolverInterface::ALIAS]] = [
                    RelationBuilderInterface::ALIAS => $relationData[RelationResolverInterface::ALIAS],
                    RelationBuilderInterface::TABLE => $this->createSubselect->execute($relation, $relationData),
                    RelationBuilderInterface::CONDITION => $this->getJoinCondition->execute($relation, $relationData),
                    RelationBuilderInterface::TYPE => $relationData[RelationResolverInterface::TYPE],
                    RelationBuilderInterface::PARENT => $relationData[RelationResolverInterface::PARENT]
                ];
            }
        }

        return $selectRelations;
    }

    public function isValid(array $relation): bool
    {
        return isset($relation[RelationResolverInterface::TYPE])
            && isset($relation[RelationResolverInterface::ALIAS])
            && isset($relation[RelationResolverInterface::EXPRESSION])
            && isset($relation[RelationResolverInterface::PARENT])
            && $relation[RelationResolverInterface::EXPRESSION] instanceof Select;
    }
}
