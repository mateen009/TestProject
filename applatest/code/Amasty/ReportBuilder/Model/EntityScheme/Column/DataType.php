<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme\Column;

class DataType
{
    const DATE = 'date';
    const DATETIME = 'datetime';
    const TIMESTAMP = 'timestamp';
    const INTEGER = 'int';
    const TEXT = 'text';
    const DECIMAL = 'decimal';
    const VARCHAR = 'varchar';
    const BOOLEAN = 'boolean';

    private $typeMap = [
        'smallint' => 'int',
        'bigint' => 'int'
    ];

    public function getTypesMap(): array
    {
        return $this->typeMap;
    }
}
