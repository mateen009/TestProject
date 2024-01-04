<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnModifiers;

use Magento\Framework\Exception\LocalizedException;

interface ColumnModifierInterface
{
    /**
     * Modify column data.
     *
     * @param string $columnId
     * @param array $columnData
     * @return array
     * @throws LocalizedException
     */
    public function modify(string $columnId, array $columnData): array;
}
