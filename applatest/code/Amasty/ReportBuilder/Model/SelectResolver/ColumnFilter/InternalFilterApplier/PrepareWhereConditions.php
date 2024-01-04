<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnFilter\InternalFilterApplier;

use Amasty\ReportBuilder\Model\ResourceModel\Report as ReportResource;

class PrepareWhereConditions
{
    /**
     * @var ReportResource
     */
    private $reportResource;

    public function __construct(ReportResource $reportResource)
    {
        $this->reportResource = $reportResource;
    }

    /**
     * @param string $alias
     * @param array $conditions
     * @return string
     */
    public function execute(string $alias, array $conditions): string
    {
        $connection = $this->reportResource->getConnection();
        $whereConditions = [];
        foreach ($conditions as $key => $condition) {
            $whereConditions[] = $connection->prepareSqlCondition($alias, [$key => $condition]);
        }

        return implode(' AND ', $whereConditions);
    }
}
