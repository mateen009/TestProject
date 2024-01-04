<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme\Relation;

class Type
{
    /**
     * Available types of Relation
     */
    public const TYPE_COLUMN = 'column';
    public const TYPE_TABLE = 'table';
    public const TYPE_ATTRIBUTE = 'attribute';

    /**
     * Relation behaviours.
     * Algorithm of the same behaviour may differ by depends on a Type.
     */
    public const ONE_TO_ONE = 'one_to_one';
    public const MANY_TO_ONE = 'many_to_one';
    public const ONE_TO_MANY = 'one_to_many';
    public const MANY_TO_MANY = 'many_to_many';
}
