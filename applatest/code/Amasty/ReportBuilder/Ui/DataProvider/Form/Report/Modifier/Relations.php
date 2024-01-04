<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Ui\DataProvider\Form\Report\Modifier;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Exception\NotExistTableException;
use Amasty\ReportBuilder\Model\EntityScheme\Provider;
use Amasty\ReportBuilder\Model\ReportRegistry;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class Relations implements ModifierInterface
{
    const SCHEME_DATA_KEY = 'scheme_relations';
    const SCHEME_DATA_NAME = 'entity_name';
    const SCHEME_DATA_RELATIONS_QTY = 'relationsQty';

    /**
     * @var ReportRegistry
     */
    private $reportRegistry;

    /**
     * @var Provider
     */
    private $schemeProvider;

    public function __construct(
        ReportRegistry $reportRegistry,
        Provider $schemeProvider
    ) {
        $this->reportRegistry = $reportRegistry;
        $this->schemeProvider = $schemeProvider;
    }

    public function modifyData(array $data)
    {
        $report = $this->reportRegistry->getReport();

        $relations = [];

        foreach ($report->getRelationScheme() as $relationData) {
            $entityName = $relationData[ReportInterface::SCHEME_SOURCE_ENTITY];
            $relatedEntity = $relationData[ReportInterface::SCHEME_ENTITY];

            if (!isset($relations[$entityName])) {
                $relations[$entityName] = $this->getEntityData($entityName);
            }

            if (!isset($relations[$relatedEntity])) {
                $relations[$relatedEntity] = $this->getEntityData($relatedEntity);
            }
        }

        if (empty($relations) && $report->getMainEntity()) {
            $relations[] = $this->getEntityData($report->getMainEntity());
        }

        $data[$report->getReportId()][self::SCHEME_DATA_KEY] = array_values($relations);

        return $data;
    }

    public function modifyMeta(array $meta)
    {
        return $meta;
    }

    private function getEntityData(string $entityName): array
    {
        $scheme = $this->schemeProvider->getEntityScheme();
        try {
            $relatedEntities = $scheme->getEntityByName($entityName)->getRelatedEntities();
        } catch (NotExistTableException $e) {
            return [];
        }

        return [
            self::SCHEME_DATA_NAME => $entityName,
            self::SCHEME_DATA_RELATIONS_QTY => count($relatedEntities)
        ];
    }
}
