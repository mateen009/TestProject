<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnBuilder\Eav\Relation;

use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolverInterface;
use Amasty\ReportBuilder\Model\SelectResolver\RelationBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\Store;

/**
 * EAV value table relation builder with store scope value.
 */
class StoreDataBuilder implements BuilderInterface
{
    /**
     * @var ResourceConnection
     */
    private $connectionResource;

    /**
     * @var StoreIdRegistry
     */
    private $storeIdRegistry;

    public function __construct(
        ResourceConnection $connectionResource,
        StoreIdRegistry $storeIdRegistry
    ) {
        $this->connectionResource = $connectionResource;
        $this->storeIdRegistry = $storeIdRegistry;
    }

    public function execute(array $columnData, string $linkedField, string $indexField, string $tableName): array
    {
        $tableAlias = sprintf('%s_store_table', $columnData[ColumnResolverInterface::ALIAS]);

        return [
            RelationBuilder::TYPE => \Zend_Db_Select::LEFT_JOIN,
            RelationBuilder::ALIAS => $tableAlias,
            RelationBuilder::TABLE => $tableName,
            RelationBuilder::COLUMNS => sprintf('%s.value', $tableAlias),
            RelationBuilder::CONDITION => sprintf(
                '%s.%s = %s.%s AND %1$s.attribute_id = \'%d\' AND %1$s.store_id = %d',
                $tableAlias,
                $indexField,
                $columnData[ColumnResolverInterface::ENTITY_NAME],
                $linkedField,
                $columnData[ColumnResolverInterface::ATTRIBUTE_ID],
                $this->storeIdRegistry->getStoreId()
            )
        ];
    }

    public function isApplicable(string $tableName): bool
    {
        $storeId = $this->storeIdRegistry->getStoreId();

        return $storeId !== null
            && $storeId !== Store::DEFAULT_STORE_ID
            && $this->connectionResource->getConnection()->tableColumnExists($tableName, 'store_id');
    }
}
