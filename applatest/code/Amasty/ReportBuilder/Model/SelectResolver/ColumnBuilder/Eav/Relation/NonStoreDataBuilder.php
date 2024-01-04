<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnBuilder\Eav\Relation;

use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolverInterface;
use Amasty\ReportBuilder\Model\SelectResolver\RelationBuilder;
use Magento\Framework\App\ResourceConnection;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnBuilder\Eav\Relation\BuilderInterface;

/**
 * Usual EAV value table relation builder.
 * If EAV have store based scope then this builder shouldn't be used
 */
class NonStoreDataBuilder implements BuilderInterface
{
    /**
     * @var ResourceConnection
     */
    private $connectionResource;

    public function __construct(
        ResourceConnection $connectionResource
    ) {
        $this->connectionResource = $connectionResource;
    }

    public function execute(array $columnData, string $linkedField, string $indexField, string $tableName): array
    {
        $tableAlias = sprintf('%s_table', $columnData[ColumnResolverInterface::ALIAS]);

        return [
            RelationBuilder::TYPE => \Zend_Db_Select::LEFT_JOIN,
            RelationBuilder::ALIAS => $tableAlias,
            RelationBuilder::TABLE => $tableName,
            RelationBuilder::COLUMNS => sprintf('%s.value', $tableAlias),
            RelationBuilder::CONDITION => sprintf(
                '%s.%s = %s.%s AND %1$s.attribute_id = \'%d\'',
                $tableAlias,
                $indexField,
                $columnData[ColumnResolverInterface::ENTITY_NAME],
                $linkedField,
                $columnData[ColumnResolverInterface::ATTRIBUTE_ID]
            )
        ];
    }

    public function isApplicable(string $tableName): bool
    {
        return !$this->connectionResource->getConnection()->tableColumnExists($tableName, 'store_id');
    }
}
