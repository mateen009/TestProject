<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Report\EntityDataModifiers;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Api\EntityInterface;

class ColumnsVisibilityModifier implements EntityDataModifierInterface
{
    public function modifyData(array $entityData): array
    {
        if (!empty($entityData[EntityInterface::COLUMNS])) {
            foreach ($entityData[EntityInterface::COLUMNS] as $key => $column) {
                $isHidden = $column[ColumnInterface::HIDDEN] ?? false;

                if ($isHidden) {
                    unset($entityData[EntityInterface::COLUMNS][$key]);
                }
            }
        }

        return $entityData;
    }
}
