<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme\Builder\Filter\Column;

use Amasty\ReportBuilder\Api\EntityInterface;
use Amasty\ReportBuilder\Model\ResourceModel\Table\IsColumnExist as IsColumnExistResource;

class IsSimpleColumnExist implements IsColumnExistInterface
{
    /**
     * @var IsColumnExistResource
     */
    private $isColumnExistResource;

    public function __construct(IsColumnExistResource $isColumnExistResource)
    {
        $this->isColumnExistResource = $isColumnExistResource;
    }

    /**
     * @param array $schemeData
     * @param string $entityName
     * @param string $columnName
     * @return bool
     */
    public function execute(array $schemeData, string $entityName, string $columnName): bool
    {
        $tableName = $schemeData[$entityName][EntityInterface::MAIN_TABLE];
        return $this->isColumnExistResource->execute($tableName, $columnName);
    }
}
