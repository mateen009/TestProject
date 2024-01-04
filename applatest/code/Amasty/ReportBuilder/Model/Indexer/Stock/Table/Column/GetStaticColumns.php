<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Indexer\Stock\Table\Column;

use Magento\Framework\DB\Ddl\Table;

class GetStaticColumns implements GetColumnsInterface
{
    const SKU_COLUMN = 'sku';
    const PRODUCT_ID_COLUMN = 'product_id';
    const TOTAL_QTY_COLUMN = 'total_qty';
    const STOCK_STATUS_DEFAULT_COLUMN = 'stock_status_default';
    const QTY_DEFAULT_COLUMN = 'qty_default';

    public function execute(): array
    {
        return [
            self::SKU_COLUMN => [
                Table::TYPE_TEXT,
                64,
                [
                    Table::OPTION_PRIMARY => false,
                    Table::OPTION_NULLABLE => false
                ],
                'SKU'
            ],
            self::PRODUCT_ID_COLUMN => [
                Table::TYPE_INTEGER,
                null,
                [
                    Table::OPTION_PRIMARY => false,
                    Table::OPTION_NULLABLE => false,
                    Table::OPTION_UNSIGNED => true
                ],
                'Product Id'
            ],
            self::TOTAL_QTY_COLUMN => [
                Table::TYPE_DECIMAL,
                null,
                [
                    Table::OPTION_UNSIGNED => false,
                    Table::OPTION_NULLABLE => false,
                    Table::OPTION_DEFAULT => 0,
                    Table::OPTION_PRECISION => 10,
                    Table::OPTION_SCALE => 4
                ],
                'Total Quantity'
            ],
            self::STOCK_STATUS_DEFAULT_COLUMN => [
                Table::TYPE_BOOLEAN,
                null,
                [
                    Table::OPTION_NULLABLE => false,
                ],
                'Stock Status (Default Stock)'
            ],
            self::QTY_DEFAULT_COLUMN => [
                Table::TYPE_DECIMAL,
                null,
                [
                    Table::OPTION_UNSIGNED => false,
                    Table::OPTION_NULLABLE => false,
                    Table::OPTION_DEFAULT => 0,
                    Table::OPTION_PRECISION => 10,
                    Table::OPTION_SCALE => 4
                ],
                'Quantity (Default Stock)'
            ]
        ];
    }
}
