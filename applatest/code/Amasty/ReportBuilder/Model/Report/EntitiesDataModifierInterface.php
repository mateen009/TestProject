<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Report;

interface EntitiesDataModifierInterface
{
    public function modify(array $entitiesData): array;
}
