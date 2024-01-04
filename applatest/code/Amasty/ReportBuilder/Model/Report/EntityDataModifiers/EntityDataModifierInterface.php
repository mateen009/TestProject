<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Report\EntityDataModifiers;

interface EntityDataModifierInterface
{
    public function modifyData(array $entityData): array;
}
