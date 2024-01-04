<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Provider;
use Amasty\ReportBuilder\Model\EntityScheme\Relation\Type;
use Amasty\ReportBuilder\Model\ReportResolver;
use Magento\Framework\Exception\LocalizedException;

class EntitySimpleRelationResolver
{
    /**
     * @var Provider
     */
    private $provider;

    /**
     * @var ReportResolver
     */
    private $reportResolver;

    public function __construct(
        Provider $provider,
        ReportResolver $reportResolver
    ) {
        $this->provider = $provider;
        $this->reportResolver = $reportResolver;
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function resolve(): array
    {
        $report = $this->reportResolver->resolve();
        return $this->getSimpleRelations($report->getRelationScheme(), $report->getMainEntity());
    }

    /**
     * @param array $relations
     * @param string $mainNode
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getSimpleRelations(array $relations, string $mainNode): array
    {
        $report = $this->reportResolver->resolve();
        $simpleRelations = [];

        if ($report->getMainEntity() == $mainNode) {
            $simpleRelations[] = $mainNode;
        }

        foreach ($relations as $key => $relationData) {
            $entityName = $relationData[ReportInterface::SCHEME_SOURCE_ENTITY];
            $relatedEntityName = $relationData[ReportInterface::SCHEME_ENTITY];
            unset($relations[$key]);

            $entity = $this->provider->getEntityScheme()->getEntityByName($entityName);

            if (in_array($entityName, $simpleRelations)
                && $entity->getRelation($relatedEntityName)->getRelationshipType() == Type::ONE_TO_ONE
            ) {
                $simpleRelations[] = $relatedEntityName;
                // phpcs:ignore
                $simpleRelations = array_merge(
                    $simpleRelations,
                    $this->getSimpleRelations($relations, $relatedEntityName)
                );
            }
        }

        return $simpleRelations;
    }
}
