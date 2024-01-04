<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Indexer\Stock\Table\Column;

use Amasty\ReportBuilder\Model\Indexer\Stock\GetStocks;
use Amasty\ReportBuilder\Model\Indexer\Stock\Table\Column\Msi\GetQtyColumnAlias;
use Amasty\ReportBuilder\Model\Indexer\Stock\Table\Column\Msi\GetStockColumnAlias;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Module\Manager as ModuleManager;

class GetMsiColumns implements GetColumnsInterface
{
    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @var GetStocks
     */
    private $getStocks;

    /**
     * @var GetStockColumnAlias
     */
    private $getStockColumnAlias;

    /**
     * @var GetQtyColumnAlias
     */
    private $getQtyColumnAlias;

    public function __construct(
        ModuleManager $moduleManager,
        GetStocks $getStocks,
        GetStockColumnAlias $getStockColumnAlias,
        GetQtyColumnAlias $getQtyColumnAlias
    ) {
        $this->moduleManager = $moduleManager;
        $this->getStocks = $getStocks;
        $this->getStockColumnAlias = $getStockColumnAlias;
        $this->getQtyColumnAlias = $getQtyColumnAlias;
    }

    public function execute(): array
    {
        $columns = [];
        if ($this->moduleManager->isEnabled('Magento_Inventory')) {
            foreach ($this->getStocks->execute() as $stockId => $stockName) {
                $columns[$this->getStockColumnAlias->execute($stockId)] = [
                    Table::TYPE_BOOLEAN,
                    null,
                    [
                        Table::OPTION_NULLABLE => true,
                    ],
                    sprintf('Stock Status (%s)', $stockName)
                ];
                $columns[$this->getQtyColumnAlias->execute($stockId)] = [
                    Table::TYPE_DECIMAL,
                    null,
                    [
                        Table::OPTION_UNSIGNED => false,
                        Table::OPTION_NULLABLE => true,
                        Table::OPTION_DEFAULT => 0,
                        Table::OPTION_PRECISION => 10,
                        Table::OPTION_SCALE => 4
                    ],
                    sprintf('Quantity (%s)', $stockName)
                ];
            }
        }

        return $columns;
    }
}
