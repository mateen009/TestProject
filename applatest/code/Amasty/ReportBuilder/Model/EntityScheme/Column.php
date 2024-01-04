<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Column\AggregationType;
use Amasty\ReportBuilder\Model\EntityScheme\Column\ColumnType;
use Amasty\ReportBuilder\Model\EntityScheme\Column\DataType;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validator\UniversalFactory;

class Column extends DataObject implements ColumnInterface
{
    /**
     * @var UniversalFactory
     */
    private $universalFactory;

    public function __construct(
        UniversalFactory $universalFactory,
        array $data = []
    ) {
        if (isset($data['config'])) {
            $this->init($data['config']);
        }

        parent::__construct($data);
        $this->universalFactory = $universalFactory;
    }

    public function init(array $columnConfig): void
    {
        if (!isset($columnConfig[ColumnInterface::NAME])) {
            throw new LocalizedException(__('Name is required field for column'));
        }

        $this->setData($columnConfig);

        $this->prepareFrontendInput();

        $this->prepareAggregationType();
    }

    private function prepareFrontendInput(): void
    {
        if ($this->getFrontendModel()) {
            return;
        }
        $type = $this->getFrontendInput() ?: $this->getType();
        $backendType = $this->getBackendType() ?: $this->getType();

        if ($this->getOptions() || $this->getSourceModel()) {
            $this->setFrontendModel('select');
        } elseif ($this->getPrimary() || in_array($backendType, [DataType::DECIMAL, DataType::INTEGER])) {
            $this->setFrontendModel('textRange');
        } elseif (in_array($type, [DataType::DATE, DataType::DATETIME, DataType::TIMESTAMP])) {
            $this->setFrontendModel('dateRange');
        } else {
            $this->setFrontendModel('text');
        }
    }

    private function prepareAggregationType(): void
    {
        if (!$this->getData(ColumnInterface::AGGREGATION_TYPE)) {
            switch (true) {
                case in_array($this->getFrontendModel(), ['select', 'multiselect']):
                case $this->getType() == DataType::DATE:
                case $this->getType() == DataType::DATETIME:
                case $this->getType() == DataType::TIMESTAMP:
                    $type = AggregationType::TYPE_MAX;
                    break;
                case $this->getType() == DataType::TEXT:
                case $this->getType() == DataType::VARCHAR:
                    $type = AggregationType::TYPE_GROUP_CONCAT;
                    break;
                case $this->getType() == DataType::DECIMAL:
                    $type = AggregationType::TYPE_SUM;
                    break;
                case $this->getType() == DataType::INTEGER:
                    $type = AggregationType::TYPE_COUNT;
                    break;
                case $this->getType() == DataType::BOOLEAN: // TODO: verify aggregation type for boolean CAT-14418
                default:
                    $type = AggregationType::TYPE_NONE;
                    break;
            }

            $this->setAggregationType($type);
        }
    }

    public function getAvailableAggregationTypes(): array
    {
        if (in_array($this->getType(), [DataType::DATE, DataType::DATETIME, DataType::TIMESTAMP])) {
            return [
                AggregationType::TYPE_NONE,
                AggregationType::TYPE_MAX,
                AggregationType::TYPE_MIN,
                AggregationType::TYPE_AVG
            ];
        } elseif ($this->getType() === DataType::BOOLEAN) {
            return [
                AggregationType::TYPE_NONE,
                AggregationType::TYPE_MAX,
                AggregationType::TYPE_MIN
            ];
        } elseif ($this->getFrontendModel() == 'textRange') {
            return [
                AggregationType::TYPE_NONE,
                AggregationType::TYPE_MAX,
                AggregationType::TYPE_MIN,
                AggregationType::TYPE_AVG,
                AggregationType::TYPE_SUM,
                AggregationType::TYPE_COUNT,
            ];
        }

        return [
            AggregationType::TYPE_NONE,
            AggregationType::TYPE_MAX,
            AggregationType::TYPE_MIN,
            AggregationType::TYPE_AVG,
            AggregationType::TYPE_SUM,
            AggregationType::TYPE_COUNT,
            AggregationType::TYPE_GROUP_CONCAT
        ];
    }

    public function setTitle(string $title): void
    {
        $this->setData(ColumnInterface::TITLE, $title);
    }

    public function getTitle(): string
    {
        return (string)$this->getData(ColumnInterface::TITLE);
    }

    public function getCustomTitle(): string
    {
        return (string)$this->getData(ColumnInterface::CUSTOM_TITLE);
    }

    public function setCustomTitle(string $customTitle): void
    {
        $this->setData(ColumnInterface::CUSTOM_TITLE, $customTitle);
    }

    public function setName(string $name): void
    {
        $this->setData(ColumnInterface::NAME, $name);
    }

    public function getName(): string
    {
        return $this->getData(ColumnInterface::NAME);
    }

    public function setType(string $type): void
    {
        $this->setData(ColumnInterface::TYPE, $type);
    }

    public function getType(): string
    {
        return (string)$this->getData(ColumnInterface::TYPE);
    }

    public function setSourceModel(string $sourceModel): void
    {
        $this->setData(ColumnInterface::SOURCE_MODEL, $sourceModel);
    }

    public function getSourceModel(): ?string
    {
        return $this->getData(ColumnInterface::SOURCE_MODEL);
    }

    public function setOptions(array $options): void
    {
        $this->setData(ColumnInterface::OPTIONS, $options);
    }

    public function getOptions(): ?array
    {
        return $this->getData(ColumnInterface::OPTIONS) ?: [];
    }

    public function setAggregationType(string $aggregationType): void
    {
        $this->setData(ColumnInterface::AGGREGATION_TYPE, $aggregationType);
    }

    public function getAggregationType(): string
    {
        $aggregationType = $this->getData(ColumnInterface::AGGREGATION_TYPE);
        $availableAggregationTypes = $this->getAvailableAggregationTypes();
        return in_array($aggregationType, $availableAggregationTypes) ? $aggregationType : AggregationType::TYPE_NONE;
    }

    public function getPrimary(): bool
    {
        return (bool) $this->getData(ColumnInterface::PRIMARY);
    }

    public function setPrimary(bool $primary): void
    {
        $this->setData(ColumnInterface::PRIMARY, $primary);
    }

    public function getFrontendModel(): string
    {
        return (string)$this->getData(ColumnInterface::FRONTEND_MODEL);
    }

    public function setFrontendModel(string $frontendModel): void
    {
        $this->setData(ColumnInterface::FRONTEND_MODEL, $frontendModel);
    }

    public function setUseForPeriod(bool $useForPeriod): void
    {
        $this->setData(ColumnInterface::USE_FOR_PERIOD, $useForPeriod);
    }

    public function getUseForPeriod(): bool
    {
        return (bool) $this->getData(ColumnInterface::USE_FOR_PERIOD);
    }

    public function getSource(): OptionSourceInterface
    {
        return $this->universalFactory->create($this->getSourceModel());
    }

    public function getPosition(): int
    {
        return (int) $this->getData(ColumnInterface::POSITION);
    }

    public function setPosition(int $position): void
    {
        $this->setData(ColumnInterface::POSITION, $position);
    }

    public function getEntityName(): ?string
    {
        return $this->getData(ColumnInterface::ENTITY_NAME);
    }

    public function setEntityName(string $entityName): void
    {
        $this->setData(ColumnInterface::ENTITY_NAME, $entityName);
    }

    public function getAlias(): string
    {
        return sprintf('%s_%s', $this->getEntityName(), $this->getName());
    }

    public function getAttributeId(): ?int
    {
        $attributeId = $this->getData(self::ATTRIBUTE_ID);
        return  $attributeId ? (int) $attributeId : null;
    }

    public function getColumnId(): string
    {
        return sprintf('%s.%s', $this->getEntityName(), $this->getName());
    }

    public function setColumnType(string $columnType): void
    {
        $this->setData(ColumnInterface::COLUMN_TYPE, $columnType);
    }

    public function getColumnType(): string
    {
        return (string) $this->getData(ColumnInterface::COLUMN_TYPE);
    }

    public function setLink(string $link): void
    {
        $this->setData(ColumnInterface::LINK, $link);
    }

    public function getLink(): string
    {
        return (string) $this->getData(ColumnInterface::LINK);
    }

    public function getParentColumn(): ?ColumnInterface
    {
        return $this->getData(ColumnInterface::PARENT_COLUMN);
    }

    public function setParentColumn(ColumnInterface $column): void
    {
        $this->setData(ColumnInterface::PARENT_COLUMN, $column);
    }

    public function setCustomExpression(string $customExpression): void
    {
        $this->setData(ColumnInterface::CUSTOM_EXPRESSION, $customExpression);
    }

    public function getCustomExpression(): string
    {
        return (string) $this->getData(ColumnInterface::CUSTOM_EXPRESSION);
    }

    public function toArray(array $keys = [])
    {
        $data = parent::toArray($keys);

        $eavAttributeKey = 'eav_attribute';
        if (empty($keys) || in_array($eavAttributeKey, $keys)) {
            $data[$eavAttributeKey] = $this->getColumnType() === ColumnType::EAV_TYPE;
        }

        return $data;
    }
}
