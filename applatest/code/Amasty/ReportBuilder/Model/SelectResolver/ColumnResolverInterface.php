<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver;

interface ColumnResolverInterface
{
    const ALIAS = 'alias';
    const EXPRESSION = 'expression';
    const EXPRESSION_INTERNAL = 'expression_internal';
    const ENTITY_NAME = 'entity_name';
    const ATTRIBUTE_ID = 'attribute_id';

    /**
     * Get all prepared columns of a report for building sql query
     *
     * @return array
     */
    public function resolve(): array;
}
