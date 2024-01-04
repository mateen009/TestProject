<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\ResourceModel\Indexer\Stock\Select;

use Amasty\ReportBuilder\Model\Indexer\Stock\GetStocks;
use Amasty\ReportBuilder\Model\Indexer\Stock\Table\Column\GetStaticColumns;
use Amasty\ReportBuilder\Model\Indexer\Stock\Table\Column\Msi\GetQtyColumnAlias;
use Amasty\ReportBuilder\Model\Indexer\Stock\Table\GetMsiTableName;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Zend_Db_Expr;

class TotalQtyBuilder implements BuilderInterface
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
     * @var GetQtyColumnAlias
     */
    private $getQtyColumnAlias;

    public function __construct(
        ResourceConnection $resourceConnection,
        GetStocks $getStocks,
        GetMsiTableName $getMsiTableName,
        GetQtyColumnAlias $getQtyColumnAlias
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->getStocks = $getStocks;
        $this->getMsiTableName = $getMsiTableName;
        $this->getQtyColumnAlias = $getQtyColumnAlias;
    }

    public function execute(Select $select): array
    {
        $qtyColumns = [$this->getOriginalColumnExpr($select, GetStaticColumns::QTY_DEFAULT_COLUMN)];

        foreach ($this->getStocks->execute() as $stockId => $stockName) {
            $stockId = (int)$stockId;
            $tableName = $this->resourceConnection->getTableName(
                $this->getMsiTableName->execute($stockId)
            );
            if ($this->resourceConnection->getConnection()->isTableExists($tableName)) {
                $qtyColumns[] = $this->getOriginalColumnExpr($select, $this->getQtyColumnAlias->execute($stockId));
            }
        }

        $select->columns([GetStaticColumns::TOTAL_QTY_COLUMN => new Zend_Db_Expr(implode('+', $qtyColumns))]);

        return [GetStaticColumns::TOTAL_QTY_COLUMN];
    }

    private function getOriginalColumnExpr(Select $select, string $columnAlias): Zend_Db_Expr
    {
        $columns = $select->getPart(Select::COLUMNS);
        foreach ($columns as $columnDefinition) {
            if (isset($columnDefinition[2]) && $columnDefinition[2] === $columnAlias) {
                return $this->getNullExpr(sprintf('%s.%s', $columnDefinition[0], $columnDefinition[1]));
            }
        }

        return $this->getNullExpr($columnAlias);
    }

    private function getNullExpr(string $columnName): Zend_Db_Expr
    {
        return $this->resourceConnection->getConnection()->getIfNullSql($columnName);
    }
}
