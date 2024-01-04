<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\ResourceModel\Indexer\Eav;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DB\Select;

abstract class AbstractType extends \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\AbstractEav
{
    /**
     * @param null $entityIds
     * @param null $attributeId
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _prepareIndex($entityIds = null, $attributeId = null): void
    {
        $linkField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();

        $select = $this->getConnection()->select()
            ->from(['catalog_product' => $this->getSourceTable()])
            ->where($linkField . ' IN (?)', $entityIds)
            ->reset(Select::COLUMNS)
            ->columns([
                'value_id',
                'attribute_id',
                'store_id',
                'entity_id' => $linkField,
                'value',
                'source_id' => $linkField
            ]);

        $this->insertFromSelect(
            $select,
            $this->getIdxTable(),
            ['value_id', 'attribute_id', 'store_id', 'entity_id', 'value', 'source_id']
        );
    }

    abstract protected function getSourceTable();

    protected function _prepareRelationIndex($parentIds = null): void
    {
        $connection = $this->getConnection();
        $query = $connection->insertFromSelect(
            $this->prepareRelationIndexSelect($parentIds),
            $this->getIdxTable(),
            ['attribute_id', 'store_id', 'entity_id', 'value', 'source_id'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_IGNORE
        );
        $connection->query($query);
    }

    private function prepareRelationIndexSelect(array $parentIds = null): Select
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            ['relation' => $this->getTable('catalog_product_relation')]
        )->joinLeft(
            ['entity' => $this->getIdxTable()],
            'entity.entity_id = relation.child_id'
        )->reset(
            Select::COLUMNS
        )->columns(
            [
                'attribute_id' => 'entity.attribute_id',
                'store_id' => 'entity.store_id',
                'entity_id' => 'relation.parent_id',
                'value' => 'entity.value',
                'source_id' => 'relation.child_id'
            ]
        )->where('relation.parent_id IS NOT NULL');

        if ($parentIds !== null) {
            $select->where('entity.entity_id IN(?)', $parentIds);
        }

        return $select;
    }

    protected function _removeNotVisibleEntityFromIndex()
    {
        return false;
    }
}
