<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\RelationModifiers;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Provider;
use Amasty\ReportBuilder\Model\EntityScheme\Relation\JoinType;
use Amasty\ReportBuilder\Model\EntityScheme\Relation\Type;
use Amasty\ReportBuilder\Model\SelectResolver\RelationModifiers\TypeTableModifier\CreateSubselect;
use Amasty\ReportBuilder\Model\SelectResolver\RelationResolver;

class TypeTableModifier implements RelationModifierInterface
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
     * @var JoinType
     */
    private $joinType;

    public function __construct(
        Provider $provider,
        CreateSubselect $createSubselect,
        JoinType $joinType
    ) {
        $this->provider = $provider;
        $this->createSubselect = $createSubselect;
        $this->joinType = $joinType;
    }

    public function modify(array $relations): array
    {
        $entityScheme = $this->provider->getEntityScheme();
        foreach ($relations as $key => $relation) {
            if (!isset($relation[ReportInterface::SCHEME_SOURCE_ENTITY])) {
                continue;
            }
            $sourceEntity = $entityScheme->getEntityByName($relation[ReportInterface::SCHEME_SOURCE_ENTITY]);
            $relationScheme = $sourceEntity->getRelation($relation[ReportInterface::SCHEME_ENTITY]);
            if ($relationScheme->getType() == Type::TYPE_TABLE) {
                $relations[$key] = [
                    RelationResolver::TYPE => $this->joinType->getJoinForSelect($relationScheme->getJoinType()),
                    RelationResolver::ALIAS => $relation[ReportInterface::SCHEME_ENTITY],
                    RelationResolver::PARENT => $sourceEntity->getName(),
                    RelationResolver::EXPRESSION => $this->createSubselect->execute(
                        $entityScheme,
                        $relationScheme,
                        $relation
                    )
                ];
            }
        }

        return $relations;
    }
}
