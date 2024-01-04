<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme\Builder\Filter\Column;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Api\EntityInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Column\ColumnType;

class IsColumnExist implements IsColumnExistInterface
{
    /**
     * @var IsColumnExistInterface
     */
    private $isColumnExistDefault;

    /**
     * @var IsColumnExistInterface[]
     */
    private $isColumnExistPool;

    public function __construct(
        IsColumnExistInterface $isColumnExistDefault,
        array $isColumnExistPool = []
    ) {
        $this->isColumnExistDefault = $isColumnExistDefault;
        $this->isColumnExistPool = $isColumnExistPool;
    }

    public function execute(array $schemeData, string $entityName, string $columnName): bool
    {
        $columnType = $schemeData[$entityName][EntityInterface::COLUMNS][$columnName][ColumnInterface::COLUMN_TYPE]
            ?? ColumnType::DEFAULT_TYPE;
        $isColumnExist = $this->isColumnExistPool[$columnType] ?? $this->isColumnExistDefault;
        return $isColumnExist->execute($schemeData, $entityName, $columnName);
    }
}
