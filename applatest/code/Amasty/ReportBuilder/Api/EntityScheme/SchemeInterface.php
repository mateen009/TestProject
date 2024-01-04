<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Api\EntityScheme;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Api\EntityInterface;
use Magento\Framework\Data\OptionSourceInterface;

interface SchemeInterface
{
    /**
     * Method returns an array of all information for entity(include list of columns)
     *
     * @param string $entityName
     * @return EntityInterface
     */
    public function getEntityByName(string $entityName): ?EntityInterface;

    /**
     * Method returns an array of all information for column
     *
     * @param string $columnId like "entity_name.column_name"
     * @return array
     */
    public function getColumnById(string $columnId): ?ColumnInterface;

    /**
     * Method creates collection of entities from scheme configuration array
     *
     * @param array $schemeConfiguration
     */
    public function init(array $schemeConfiguration): void;

    /**
     * Method creates an object of EntityInterface from config and adds it to entities collection
     *
     * @param string $entityName
     * @param array $config
     * @return EntityInterface
     */
    public function addEntity(string $entityName, array $config): EntityInterface;

    /**
     * Method returns an array of all existed entity titles by their names
     *
     * @param bool $primariesOnly
     * @return array
     */
    public function getAllEntitiesOptionArray(bool $primariesOnly = false): array;

    /**
     * Method returns an array of all existed entities objects by their names
     *
     * @return array
     */
    public function getEntitiesCollection(): array;

    /**
     * @param string $mainEntityName
     * @param array $simpleRelations
     * @return array
     */
    public function getSimpleRelations(string $mainEntityName, array $simpleRelations = []): array;
}
