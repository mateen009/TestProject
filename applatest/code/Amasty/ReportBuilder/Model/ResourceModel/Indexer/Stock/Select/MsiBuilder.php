<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\ResourceModel\Indexer\Stock\Select;

use Amasty\ReportBuilder\Model\Indexer\Stock\GetStocks;
use Amasty\ReportBuilder\Model\Indexer\Stock\Table\Column\Msi\GetQtyColumnAlias;
use Amasty\ReportBuilder\Model\Indexer\Stock\Table\Column\Msi\GetStockColumnAlias;
use Amasty\ReportBuilder\Model\Indexer\Stock\Table\GetMsiTableName;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

class MsiBuilder implements BuilderInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var GetStocks
     */
    private $getStocks;

    /**
     * @var GetMsiTableName
     */
    private $getMsiTableName;

    /**
     * @var GetStockColumnAlias
     */
    private $getStockColumnAlias;

    /**
     * @var GetQtyColumnAlias
     */
    private $getQtyColumnAlias;

    public function __construct(
        ResourceConnection $resourceConnection,
        GetStocks $getStocks,
        GetMsiTableName $getMsiTableName,
        GetStockColumnAlias $getStockColumnAlias,
        GetQtyColumnAlias $getQtyColumnAlias
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->getStocks = $getStocks;
        $this->getMsiTableName = $getMsiTableName;
        $this->getStockColumnAlias = $getStockColumnAlias;
        $this->getQtyColumnAlias = $getQtyColumnAlias;
    }

    public function execute(Select $select): array
    {
        $joinedColumns = [];

        foreach ($this->getStocks->execute() as $stockId => $stockName) {
            $stockId = (int) $stockId;
            $tableName = $this->resourceConnection->getTableName(
                $this->getMsiTableName->execute($stockId)
            );
            if ($this->resourceConnection->getConnection()->isTableExists($tableName)) {
                $tableAlias = sprintf('is_%d', $stockId);
                $stockStatusAlias = $this->getStockColumnAlias->execute($stockId);
                $qtyAlias = $this->getQtyColumnAlias->execute($stockId);
                $select->joinLeft(
                    [$tableAlias => $tableName],
                    sprintf('%s.sku = cpe.sku', $tableAlias),
                    [
                        $stockStatusAlias => sprintf('%s.is_salable', $tableAlias),
                        $qtyAlias => sprintf('%s.quantity', $tableAlias)
                    ]
                );
                $joinedColumns[] = $stockStatusAlias;
                $joinedColumns[] = $qtyAlias;
            }
        }

        return $joinedColumns;
    }
}
