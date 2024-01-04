<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Ui\DataProvider\Form\Report\Modifier;

use Amasty\ReportBuilder\Api\EntityInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Provider;
use Amasty\ReportBuilder\Model\Report\EntitiesDataModifierInterface;
use Amasty\ReportBuilder\Model\Report\EntityProvider;
use Amasty\ReportBuilder\Model\ReportRegistry;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class Entities implements ModifierInterface
{
    const DATA_KEY = 'entities';

    /**
     * @var ReportRegistry
     */
    private $reportRegistry;

    /**
     * @var EntityProvider
     */
    private $entityProvider;

    /**
     * @var EntitiesDataModifierInterface
     */
    private $dataModifier;

    /**
     * @var Provider
     */
    private $schemeProvider;

    public function __construct(
        ReportRegistry $reportRegistry,
        EntityProvider $entityProvider,
        EntitiesDataModifierInterface $dataModifier,
        Provider $schemeProvider
    ) {
        $this->reportRegistry = $reportRegistry;
        $this->entityProvider = $entityProvider;
        $this->dataModifier = $dataModifier;
        $this->schemeProvider = $schemeProvider;
    }

    public function modifyData(array $data)
    {
        $report = $this->reportRegistry->getReport();
        if (isset($data[$report->getReportId()]) && $report->getMainEntity()) {
            $data[$report->getReportId()][self::DATA_KEY] = $this->getEntitiesData();
        }

        return $data;
    }

    public function modifyMeta(array $meta)
    {
        return $meta;
    }

    private function getEntitiesData(): array
    {
        $report = $this->reportRegistry->getReport();
        $scheme = $this->schemeProvider->getEntityScheme();
        $simpleRelations = $scheme->getSimpleRelations($report->getMainEntity());

        $entities = array_unique(array_merge([$report->getMainEntity()], $report->getAllEntities()));
        $entitiesData = $this->dataModifier->modify($this->entityProvider->getEntities($entities));

        foreach ($entitiesData as &$entityData) {
            $entityData[EntityInterface::USE_AGGREGATION] = !in_array(
                $entityData[EntityInterface::NAME],
                $simpleRelations
            );
        }

        return array_values($entitiesData);
    }
}
