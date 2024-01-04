<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\ResourceModel\Indexer\Stock\Select;

use Amasty\ReportBuilder\Model\Indexer\Stock\Table\Column\GetStaticColumns;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

class DefaultSimpleBuilder implements BuilderInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(Select $select): array
    {
        $select->join(
            ['css' => $this->resourceConnection->getTableName('cataloginventory_stock_status')],
            'css.product_id = cpe.entity_id',
            [
                GetStaticColumns::STOCK_STATUS_DEFAULT_COLUMN => 'css.stock_status',
                GetStaticColumns::QTY_DEFAULT_COLUMN => 'css.qty'
            ]
        );

        return [
            GetStaticColumns::STOCK_STATUS_DEFAULT_COLUMN,
            GetStaticColumns::QTY_DEFAULT_COLUMN
        ];
    }
}
