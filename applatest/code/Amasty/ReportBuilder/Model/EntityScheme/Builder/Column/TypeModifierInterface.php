<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme\Builder\Column;

interface TypeModifierInterface
{
    /**
     * Modify column data by column type.
     *
     * @param array $data
     * @return array
     */
    public function execute(array $data): array;
}
