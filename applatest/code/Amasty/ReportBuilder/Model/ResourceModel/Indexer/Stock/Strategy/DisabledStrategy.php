<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\ResourceModel\Indexer\Stock\Strategy;

use Amasty\ReportBuilder\Model\ResourceModel\Indexer\Stock\Select\BuilderInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;

class DisabledStrategy implements StrategyInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var BuilderInterface[]
     */
    private $selectBuilders;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    public function __construct(
        ResourceConnection $resourceConnection,
        EavConfig $eavConfig,
        MetadataPool $metadataPool,
        array $selectBuilders
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->eavConfig = $eavConfig;
        $this->metadataPool = $metadataPool;
        $this->selectBuilders = $selectBuilders;
    }

    public function filter(Select $select): void
    {
        $select->joinInner(
            ['cpei' => $this->resourceConnection->getTableName('catalog_product_entity_int')],
            sprintf(
                'cpe.%1$s = cpei.%1$s AND cpei.attribute_id = %2$d AND cpei.value = %3$d',
                $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField(),
                $this->eavConfig->getAttribute(Product::ENTITY, 'status')->getId(),
                ProductStatus::STATUS_DISABLED
            ),
            []
        );
    }

    public function getSelectBuilders(): array
    {
        return $this->selectBuilders;
    }
}
