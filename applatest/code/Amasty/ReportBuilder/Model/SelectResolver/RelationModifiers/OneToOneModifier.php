<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\RelationModifiers;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Api\EntityInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Provider;
use Amasty\ReportBuilder\Model\EntityScheme\Relation\JoinType;
use Amasty\ReportBuilder\Model\EntityScheme\Relation\Type;
use Amasty\ReportBuilder\Model\SelectResolver\RelationResolver;
use Magento\Framework\App\ResourceConnection;

class OneToOneModifier implements RelationModifierInterface
{
    /**
     * @var Provider
     */
    private $provider;

    /**
     * @var ResourceConnection
     */
    private $connectionResource;

    /**
     * @var JoinType
     */
    private $joinType;

    public function __construct(
        Provider $provider,
        ResourceConnection $connectionResource,
        JoinType $joinType
    ) {
        $this->provider = $provider;
        $this->connectionResource = $connectionResource;
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
            if ($relationScheme->getType() == Type::TYPE_COLUMN
                && $relationScheme->getRelationshipType() == Type::ONE_TO_ONE
            ) {
                $relatedEntity = $entityScheme->getEntityByName($relation[ReportInterface::SCHEME_ENTITY]);
                $joinExpression = sprintf(
                    '`%s`.%s = `%s`.%s',
                    $sourceEntity->getName(),
                    $relationScheme->getColumn(),
                    $relatedEntity->getName(),
                    $relationScheme->getReferenceColumn()
                );

                $relations[$key] = [
                    RelationResolver::TYPE => $this->joinType->getJoinForSelect($relationScheme->getJoinType()),
                    RelationResolver::TABLE => $this->connectionResource->getTableName($relatedEntity->getMainTable()),
                    RelationResolver::ALIAS => $relation[ReportInterface::SCHEME_ENTITY],
                    RelationResolver::PARENT => $sourceEntity->getName(),
                    RelationResolver::EXPRESSION => $this->getJoinExpressions($relatedEntity, $joinExpression)
                ];
            }
        }

        return $relations;
    }

    private function getJoinExpressions(EntityInterface $entity, string $joinExpression): string
    {
        $joinExpressions = [$joinExpression];
        if ($entity->getExpressions()) {
            $joinExpressions = array_merge($joinExpressions, $entity->getExpressions());
        }

        return implode(' AND ', $joinExpressions);
    }
}
