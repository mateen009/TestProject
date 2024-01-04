<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\ResourceModel\Indexer\Stock\Select;

use Amasty\ReportBuilder\Model\Indexer\Stock\Table\Column\GetStaticColumns;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;

class DefaultAggregateBuilder implements BuilderInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    public function __construct(ResourceConnection $resourceConnection, MetadataPool $metadataPool)
    {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
    }

    public function execute(Select $select): array
    {
        $catalogInventoryTable = $this->resourceConnection->getTableName('cataloginventory_stock_status');

        $compositeSelect = $this->resourceConnection->getConnection()->select()->from(
            ['cpr' => $this->resourceConnection->getTableName('catalog_product_relation')],
            [
                GetStaticColumns::STOCK_STATUS_DEFAULT_COLUMN => 'parent_css.stock_status',
                GetStaticColumns::QTY_DEFAULT_COLUMN => sprintf('SUM(%s)', 'child_css.qty'),
                'product_id' => 'parent_cpe.entity_id'
            ]
        )->join(
            ['child_css' => $catalogInventoryTable],
            'child_css.product_id = cpr.child_id',
            []
        )->join(
            ['parent_cpe' => $this->resourceConnection->getTableName('catalog_product_entity')],
            sprintf(
                'parent_cpe.%s = cpr.parent_id',
                $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField()
            ),
            []
        )->join(
            ['parent_css' => $catalogInventoryTable],
            'parent_css.product_id = parent_cpe.entity_id',
            []
        )->group('cpr.parent_id');

        $select->join(
            ['css' => $compositeSelect],
            'css.product_id = cpe.entity_id',
            [GetStaticColumns::STOCK_STATUS_DEFAULT_COLUMN, GetStaticColumns::QTY_DEFAULT_COLUMN]
        );

        return [
            GetStaticColumns::STOCK_STATUS_DEFAULT_COLUMN,
            GetStaticColumns::QTY_DEFAULT_COLUMN
        ];
    }
}
