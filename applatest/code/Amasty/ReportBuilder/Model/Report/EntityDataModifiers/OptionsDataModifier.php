<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Report\EntityDataModifiers;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Api\EntityInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Column\OptionsResolver;
use Amasty\ReportBuilder\Model\EntityScheme\Provider;

class OptionsDataModifier implements EntityDataModifierInterface
{
    /**
     * @var OptionsResolver
     */
    private $optionsResolver;

    /**
     * @var Provider
     */
    private $schemeProvider;

    public function __construct(
        Provider $schemeProvider,
        OptionsResolver $optionsResolver
    ) {
        $this->schemeProvider = $schemeProvider;
        $this->optionsResolver = $optionsResolver;
    }

    public function modifyData(array $entityData): array
    {
        $scheme = $this->schemeProvider->getEntityScheme();

        if (isset($entityData[EntityInterface::COLUMNS])) {
            foreach ($entityData[EntityInterface::COLUMNS] as &$column) {
                if (isset($column[ColumnInterface::ID])) {
                    $schemeColumn = $scheme->getColumnById($column[ColumnInterface::ID]);

                    if ($schemeColumn->getSourceModel() || $schemeColumn->getOptions()) {
                        $column[ColumnInterface::OPTIONS] = $this->optionsResolver->resolve($schemeColumn);
                    }
                }
            }
        }

        return $entityData;
    }
}
