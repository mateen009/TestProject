<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\ResourceModel\Table;

use Magento\Framework\App\ResourceConnection;

class IsTableExist
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
     * @return bool
     */
    public function execute(string $tableName): bool
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName($tableName);

        $connection->disallowDdlCache();
        $result = $connection->isTableExists($tableName);
        $connection->allowDdlCache();

        return $result;
    }
}
