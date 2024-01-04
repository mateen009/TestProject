<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme\Builder\Eav;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Api\EntityInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Exception\LocalizedException;

class AddOptions
{
    public function execute(AbstractAttribute $attribute, array &$entity, string $name): void
    {
        $options = [];

        if ($this->isNeedOptions($attribute)) {
            try {
                $source = $attribute->getSource();
                if ($source !== null) {
                    $options = $source->getAllOptions();
                }
            } catch (LocalizedException $e) {
                null; //do nothing
            }
        }

        if ($options) {
            $options = $this->prepareOptionsFormat($options);
            if (isset($entity[EntityInterface::COLUMNS][$name][ColumnInterface::OPTIONS])) {
                $entity[EntityInterface::COLUMNS][$name][ColumnInterface::OPTIONS] = array_merge(
                    $options,
                    $entity[EntityInterface::COLUMNS][$name][ColumnInterface::OPTIONS]
                );
            } else {
                $entity[EntityInterface::COLUMNS][$name][ColumnInterface::OPTIONS] = $options;
            }
        }
    }

    private function prepareOptionsFormat(array $invalidOptions): array
    {
        $validOptions = [];
        foreach ($invalidOptions as $option) {
            if (isset($option['value']) && $option['value'] && !is_array($option['value'])) {
                $validOptions[$option['value']] = $option['label'];
            }
        }

        return $validOptions;
    }

    private function isNeedOptions(AbstractAttribute $attribute): bool
    {
        $input = $attribute->getFrontendInput();

        return $input === 'select' || $input === 'multiselect';
    }
}
