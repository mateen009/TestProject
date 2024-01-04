<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\RelationBuilder;

interface JoinModifierInterface
{
    public function modify(array $relations): array;

    public function isValid(array $relation): bool;
}
