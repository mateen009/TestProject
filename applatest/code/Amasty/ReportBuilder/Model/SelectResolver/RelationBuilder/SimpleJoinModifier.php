<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\RelationBuilder;

use Amasty\ReportBuilder\Model\SelectResolver\RelationBuilderInterface;
use Amasty\ReportBuilder\Model\SelectResolver\RelationResolverInterface;

class SimpleJoinModifier implements JoinModifierInterface
{
    public function modify(array $relations): array
    {
        foreach ($relations as $relationData) {
            if ($this->isValid($relationData)) {
                $relationEntityName = $relationData[RelationResolverInterface::ALIAS];
                $relations[$relationEntityName] = [
                    RelationBuilderInterface::ALIAS => $relationEntityName,
                    RelationBuilderInterface::TABLE => $relationData[RelationResolverInterface::TABLE],
                    RelationBuilderInterface::CONDITION => $relationData[RelationResolverInterface::EXPRESSION],
                    RelationBuilderInterface::TYPE => $relationData[RelationResolverInterface::TYPE],
                    RelationBuilderInterface::PARENT => $relationData[RelationResolverInterface::PARENT]
                ];
            }
        }

        return $relations;
    }

    public function isValid(array $relationData): bool
    {
        return isset($relationData[RelationResolverInterface::TYPE])
            && isset($relationData[RelationResolverInterface::ALIAS])
            && isset($relationData[RelationResolverInterface::EXPRESSION])
            && isset($relationData[RelationResolverInterface::TABLE])
            && is_string($relationData[RelationResolverInterface::EXPRESSION]);
    }
}
