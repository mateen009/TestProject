<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver;

interface ColumnExpressionResolverInterface
{
    /**
     * Retrieve column sql expression by its alias
     *
     * @param string $columnAlias
     * @param bool $useInternal
     * @return string
     */
    public function resolve(string $columnAlias, bool $useInternal = false): string;
}
