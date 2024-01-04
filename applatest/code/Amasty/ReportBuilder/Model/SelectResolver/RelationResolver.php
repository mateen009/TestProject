<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Model\ReportResolver;

class RelationResolver implements RelationResolverInterface
{
    /**
     * @var ReportResolver
     */
    private $reportResolver;

    /**
     * @var RelationStorageInterface
     */
    private $relationStorage;

    /**
     * @var RelationValidatorInterface
     */
    private $relationValidator;

    /**
     * @var array
     */
    private $pool;

    public function __construct(
        ReportResolver $reportResolver,
        RelationStorageInterface $relationStorage,
        RelationValidatorInterface $relationValidator,
        array $pool = []
    ) {
        $this->reportResolver = $reportResolver;
        $this->relationStorage = $relationStorage;
        $this->relationValidator = $relationValidator;
        $this->pool = $pool;
    }

    public function resolve(): array
    {
        $relations = $this->relationStorage->getAllRelations();
        if (empty($relations)) {

            $relations = $this->getReportRelations();

            $this->relationValidator->execute($relations);
            foreach ($this->pool as $modifier) {
                $relations = $modifier->modify($relations);
            }

            $this->relationStorage->setRelations($relations);
        }

        return $relations;
    }

    public function getRelationByName(string $name): array
    {
        $relations = $this->resolve();

        return $relations[$name] ?? [];
    }

    private function getReportRelations(): array
    {
        $report = $this->reportResolver->resolve();
        $relations = [];

        foreach ($report->getRelationScheme() as $relation) {
            $relations[$relation[ReportInterface::SCHEME_ENTITY]] = $relation;
        }

        return $relations;
    }
}
