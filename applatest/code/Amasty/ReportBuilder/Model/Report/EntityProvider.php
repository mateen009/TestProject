<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Report;

use Amasty\ReportBuilder\Exception\NotExistTableException;
use Amasty\ReportBuilder\Model\EntityScheme\Provider;

class EntityProvider
{
    /**
     * @var Provider
     */
    private $entitySchemeProvider;

    public function __construct(Provider $entitySchemeProvider)
    {
        $this->entitySchemeProvider = $entitySchemeProvider;
    }

    public function getEntities(array $entityNames, array $entities = []): array
    {
        $entityScheme = $this->entitySchemeProvider->getEntityScheme();

        foreach ($entityNames as $entityName) {
            try {
                $entity = $entityScheme->getEntityByName($entityName);
            } catch (NotExistTableException $e) {
                continue;
            }

            if ($entity->isHidden()) {
                continue;
            }
            if (!isset($entities[$entityName])) {
                $entities[$entity->getName()] = $entity->toArray();
                // Array merge required to perform correct soritng of entities on report edit page
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                $entities = array_merge($entities, $this->getEntities($entity->getRelatedEntities(), $entities));
            }
        }

        return $entities;
    }
}
