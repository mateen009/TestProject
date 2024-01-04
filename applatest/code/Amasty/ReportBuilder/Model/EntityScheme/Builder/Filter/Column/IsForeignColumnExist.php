<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme\Builder\Filter\Column;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Api\EntityInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Builder\Filter\Entity\IsTableExistInterface;

class IsForeignColumnExist implements IsColumnExistInterface
{
    /**
     * @var IsTableExistInterface
     */
    private $isTableExist;

    /**
     * @var IsColumnExistInterface
     */
    private $isColumnExist;

    public function __construct(IsTableExistInterface $isTableExist, IsColumnExistInterface $isColumnExist)
    {
        $this->isTableExist = $isTableExist;
        $this->isColumnExist = $isColumnExist;
    }

    /**
     * @param array $schemeData
     * @param string $entityName
     * @param string $columnName
     * @return bool
     */
    public function execute(array $schemeData, string $entityName, string $columnName): bool
    {
        $link = $schemeData[$entityName][EntityInterface::COLUMNS][$columnName][ColumnInterface::LINK];
        [$referenceEntityName, $referenceColumnName] = explode('.', $link);

        return $this->isTableExist->execute($schemeData, $referenceEntityName)
            && $this->isColumnExist->execute($schemeData, $referenceEntityName, $referenceColumnName);
    }
}
