<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme\Builder;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Api\EntityInterface;
use Amasty\ReportBuilder\Api\EntityScheme\BuilderInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Builder\Eav\AddOptions;
use Amasty\ReportBuilder\Model\EntityScheme\Column\ColumnType;
use Amasty\ReportBuilder\Model\EntityScheme\Column\DataType;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

class Eav implements BuilderInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var AddOptions
     */
    private $addOptions;

    private $dataType;

    public function __construct(
        Config $config,
        AddOptions $addOptions,
        DataType $dataType
    ) {
        $this->config = $config;
        $this->addOptions = $addOptions;
        $this->dataType = $dataType;
    }

    public function build(array $data = []): array
    {
        foreach ($data as &$entity) {
            $issetEav = isset($entity[EntityInterface::EAV]) && $entity[EntityInterface::EAV];
            $entity = $issetEav ? $this->prepareEavEntity($entity) : $entity;
        }

        return $data;
    }

    private function prepareEavEntity(array $entity): array
    {
        $attributes = $this->config->getEntityAttributes($entity[EntityInterface::NAME]);
        foreach ($attributes as $name => $attribute) {
            $columnData = $this->getColumnData($attribute);

            if (isset($entity[EntityInterface::COLUMNS]) && isset($entity[EntityInterface::COLUMNS][$name])) {
                // phpcs:ignore
                $entity[EntityInterface::COLUMNS][$name] = array_merge(
                    $columnData,
                    $entity[EntityInterface::COLUMNS][$name]
                );
            } else {
                $entity[EntityInterface::COLUMNS][$name] = $columnData;
            }

            $this->addOptions->execute($attribute, $entity, $name);
        }

        return $entity;
    }

    private function getColumnData(AbstractAttribute $attribute): array
    {
        $map = $this->dataType->getTypesMap();
        return [
            ColumnInterface::COLUMN_TYPE => $attribute->getBackendType() != 'static'
                ? ColumnType::EAV_TYPE : ColumnType::DEFAULT_TYPE,
            ColumnInterface::NAME => $attribute->getAttributeCode(),
            ColumnInterface::TITLE => $attribute->getFrontendLabel() ?: $attribute->getAttributeCode(),
            ColumnInterface::TYPE => $map[$attribute->getBackendType()] ?? $attribute->getBackendType(),
            ColumnInterface::SOURCE_MODEL => (string)$attribute->getSourceModel(),
            ColumnInterface::ATTRIBUTE_ID => $attribute->getAttributeId()
        ];
    }
}
