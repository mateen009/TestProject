<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\ResourceModel\Indexer\Stock;

use Amasty\ReportBuilder\Model\Indexer\Stock\Table\Column\GetStaticColumns;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

class GetDefaultSelect
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(): Select
    {
        $select = $this->resourceConnection->getConnection()->select();
        $select->from(
            ['cpe' => $this->resourceConnection->getTableName('catalog_product_entity')],
            [
                GetStaticColumns::SKU_COLUMN => 'cpe.sku',
                GetStaticColumns::PRODUCT_ID_COLUMN => 'cpe.entity_id'
            ]
        );

        return $select;
    }
}
