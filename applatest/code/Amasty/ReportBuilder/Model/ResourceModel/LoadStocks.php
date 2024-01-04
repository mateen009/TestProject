<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

class LoadStocks
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
     * Returned all stocks.
     * @return array Stock Id => Stock Name
     */
    public function execute(): array
    {
        $select = $this->resourceConnection->getConnection()->select()->from(
            $this->resourceConnection->getTableName('inventory_stock'),
            ['stock_id', 'name']
        );

        return $this->resourceConnection->getConnection()->fetchPairs($select);
    }
}
