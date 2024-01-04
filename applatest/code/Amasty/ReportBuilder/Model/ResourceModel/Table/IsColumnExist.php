<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\ResourceModel\Table;

use Magento\Framework\App\ResourceConnection;

class IsColumnExist
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param string $tableName
     * @param string $columnName
     * @return bool
     */
    public function execute(string $tableName, string $columnName): bool
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName($tableName);

        $connection->disallowDdlCache();
        $result = $connection->tableColumnExists($tableName, $columnName);
        $connection->allowDdlCache();

        return $result;
    }
}
