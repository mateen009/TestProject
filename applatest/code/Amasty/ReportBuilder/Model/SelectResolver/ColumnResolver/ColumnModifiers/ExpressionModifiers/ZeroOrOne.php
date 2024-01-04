<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnModifiers\ExpressionModifiers;

use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolverInterface;
use Magento\Framework\App\ResourceConnection;

class ZeroOrOne implements ModifierInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function modify(string $columnId, array $selectColumnData): array
    {
        $connection = $this->resourceConnection->getConnection();
        $selectColumnData[ColumnResolverInterface::EXPRESSION] = $connection->getCheckSql(
            sprintf('%s > 0', $selectColumnData[ColumnResolverInterface::EXPRESSION]),
            1,
            0
        )->__toString();

        return $selectColumnData;
    }
}
