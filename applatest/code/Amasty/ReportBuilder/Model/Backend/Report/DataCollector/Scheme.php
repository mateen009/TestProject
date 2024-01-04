<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Backend\Report\DataCollector;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Model\Backend\Report\DataCollectorInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Provider;
use Amasty\ReportBuilder\Model\Relation\DependencyResolver;
use Magento\Framework\Serialize\Serializer\Json;

class Scheme implements DataCollectorInterface
{
    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var Provider
     */
    private $schemeProvider;

    /**
     * @var DependencyResolver
     */
    private $relationDependencyResolver;

    public function __construct(
        Json $serializer,
        Provider $schemeProvider,
        DependencyResolver $relationDependencyResolver
    ) {
        $this->serializer = $serializer;
        $this->schemeProvider = $schemeProvider;
        $this->relationDependencyResolver = $relationDependencyResolver;
    }

    public function collect(ReportInterface $report, array $inputData): array
    {
        $schemeEntitiesData = [$report->getMainEntity()];
        $relations = [];
        $columns = $report->getData(ReportInterface::COLUMNS);
        $scheme = $this->schemeProvider->getEntityScheme();

        foreach ($columns as $columnId => $columnData) {
            $column = $scheme->getColumnById($columnId);
            $column = $column->getParentColumn() ?? $column; // resolve foreign column
            if (!in_array($column->getEntityName(), $schemeEntitiesData)) {
                $dependencies = $this->relationDependencyResolver->resolve(
                    $report->getMainEntity(),
                    $column->getEntityName()
                );
                $dependencyRelations = $this->buildDependencyRelations($dependencies);
                $this->updateRelations($relations, $dependencyRelations);

                // phpcs:ignore
                $schemeEntitiesData = array_merge($schemeEntitiesData, $dependencies);
            }
        }

        $result[ReportInterface::SCHEME] = $relations;

        return $result;
    }

    private function buildDependencyRelations(array $dependencies): array
    {
        $relations = [];
        $dependenciesCount = count($dependencies);
        for ($i = 0; $i < $dependenciesCount; $i++) {
            if (isset($dependencies[$i + 1])) {
                $relations[] = [
                    ReportInterface::SCHEME_SOURCE_ENTITY => $dependencies[$i],
                    ReportInterface::SCHEME_ENTITY => $dependencies[$i + 1]
                ];
            }
        }

        return $relations;
    }

    private function updateRelations(array &$relations, array $dependencyRelations): void
    {
        foreach ($relations as $relation) {
            foreach ($dependencyRelations as $key => $dependencyRelation) {
                $result = array_diff($relation, $dependencyRelation);
                if (!$result) {
                    unset($dependencyRelations[$key]);
                }
            }
        }

        $relations = array_merge_recursive($relations, $dependencyRelations);
    }
}
