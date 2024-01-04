<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\RelationModifiers;

interface RelationModifierInterface
{
    public function modify(array $relations): array;
}
