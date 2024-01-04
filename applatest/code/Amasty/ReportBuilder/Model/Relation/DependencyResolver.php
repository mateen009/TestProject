<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Relation;

use Amasty\ReportBuilder\Api\EntityInterface;
use Amasty\ReportBuilder\Api\EntityScheme\ProviderInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Relation\Type;

class DependencyResolver
{
    const DEPENDENCY_WAYS_COUNT = 20;

    /**
     * @var ProviderInterface
     */
    private $schemeProvider;

    /**
     * @var array
     */
    private $dependencies = [];

    /**
     * @var array
     */
    private $invalidDependenciesStorage = [];

    public function __construct(ProviderInterface $schemeProvider)
    {
        $this->schemeProvider = $schemeProvider;
    }

    public function resolve(string $sourceEntityName, string $destinationEntityName): array
    {
        $this->init();
        $entityScheme = $this->schemeProvider->getEntityScheme();
        $sourceEntity = $entityScheme->getEntityByName($sourceEntityName);
        $stack = [$sourceEntity->getName()];
        $this->resolveDependencies($sourceEntity, $destinationEntityName, $stack);

        $min = self::DEPENDENCY_WAYS_COUNT;
        $minIndex = 0;
        foreach ($this->dependencies as $index => $stack) {
            if (count($stack) < $min) {
                $min = count($stack);
                $minIndex = $index;
            }
        }

        return $this->dependencies[$minIndex];
    }

    private function init(): void
    {
        $this->dependencies = [];
        $this->invalidDependenciesStorage = [];
    }

    private function resolveDependencies(
        EntityInterface $sourceEntity,
        string $destinationEntityName,
        array &$stack
    ): bool {
        $entityScheme = $this->schemeProvider->getEntityScheme();

        $currentEntityStack = $stack;
        foreach ($sourceEntity->getRelations() as $relation) {
            if ($this->validateStep($sourceEntity, $relation->getName(), $stack)) {
                if ($relation->getName() == $destinationEntityName) {
                    $stack[] = $relation->getName();
                    $this->dependencies[] = $stack;
                    return true;
                }
                if (!in_array($relation->getName(), $stack)) {
                    $stack[] = $relation->getName();
                    $found = $this->resolveDependencies(
                        $entityScheme->getEntityByName($relation->getName()),
                        $destinationEntityName,
                        $stack
                    );
                    if (!$found) {
                        $this->invalidDependenciesStorage[] = $this->getInvalidRelationKey(
                            $sourceEntity->getName(),
                            $relation->getName()
                        );
                    }
                }
                $stack = $currentEntityStack;
            }
        }

        return false;
    }

    private function validateStep(EntityInterface $parentEntity, string $currentEntityName, array $stack): bool
    {
        $invalidRelationKey = $this->getInvalidRelationKey($parentEntity->getName(), $currentEntityName);
        return !in_array($invalidRelationKey, $this->invalidDependenciesStorage)
            && !in_array($currentEntityName, $stack)
            && $this->validateRelation($parentEntity, $currentEntityName);
    }

    private function validateRelation(EntityInterface $parentEntity, string $currentEntityName): bool
    {
        $entityScheme = $this->schemeProvider->getEntityScheme();
        $currentEntity = $entityScheme->getEntityByName($currentEntityName);

        return $parentEntity->isPrimary() || $currentEntity->isPrimary()
            || $parentEntity->getRelation($currentEntityName)->getRelationshipType() == Type::ONE_TO_ONE;
    }

    private function getInvalidRelationKey(string $sourceEntityName, string $destinationEntityName): string
    {
        return sprintf('%s-%s', $sourceEntityName, $destinationEntityName);
    }
}
