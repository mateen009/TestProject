<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnBuilder\Eav;

use Amasty\ReportBuilder\Api\EntityInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Column\AggregationType;
use Amasty\ReportBuilder\Model\EntityScheme\Provider;
use Amasty\ReportBuilder\Model\ResourceModel\Report\Data\SelectFactory;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnAggregationTypeResolver;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnBuilder\Eav\Column\ExpressionResolver;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolverInterface;
use Amasty\ReportBuilder\Model\SelectResolver\RelationBuilder;
use Amasty\ReportBuilder\Model\SelectResolver\RelationStorageInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubSelectRelationBuilder
{
    const ENTITY_ID = 'entity_id';
    const AMASTY_REPORT_BUILDER_EAV_INDEX_TABLE = 'amasty_report_builder_eav_index';
    const PRODUCT_ENTITY_NAME = 'catalog_product';

    public const RELATIONS = 'relations';

    public const SELECT = 'select';

    public const COLUMNS = 'columns';

    /**
     * @var Provider
     */
    private $provider;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var array
     */
    private $entitiesWithRowId;

    /**
     * @var SelectFactory
     */
    private $selectFactory;

    /**
     * @var RelationResolver
     */
    private $attributeRelationResolver;

    /**
     * @var ExpressionResolver
     */
    private $columnExpressionResolver;

    /**
     * @var RelationStorageInterface
     */
    private $relationStorage;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AggregationType
     */
    private $aggregationType;

    /**
     * @var string
     */
    private $linkField = self::ENTITY_ID;

    public function __construct(
        Provider $provider,
        MetadataPool $metadataPool,
        SelectFactory $selectFactory,
        RelationResolver $attributeRelationResolver,
        ExpressionResolver $columnExpressionResolver,
        RelationStorageInterface $relationStorage,
        ResourceConnection $resourceConnection,
        AggregationType $aggregationType,
        array $entitiesWithRowId = []
    ) {
        $this->provider = $provider;
        $this->metadataPool = $metadataPool;
        $this->entitiesWithRowId = $entitiesWithRowId;
        $this->selectFactory = $selectFactory;
        $this->attributeRelationResolver = $attributeRelationResolver;
        $this->columnExpressionResolver = $columnExpressionResolver;
        $this->relationStorage = $relationStorage;
        $this->resourceConnection = $resourceConnection;
        $this->aggregationType = $aggregationType;
    }

    public function build(string $columnId, array $columnData): array
    {
        $tableName = $this->getColumnTableName($columnId, $columnData);
        $linkedFiled = $this->getLinkedField($columnData);
        $indexField = $this->getIndexField($columnId, $linkedFiled);
        $scheme = $this->provider->getEntityScheme();
        $entity = $scheme->getEntityByName($columnData[ColumnResolverInterface::ENTITY_NAME]);

        $content = $this->buildContent($entity, $linkedFiled, $columnData, $indexField, $tableName);

        $alias = sprintf('%s_attribute', $columnData[ColumnResolverInterface::ALIAS]);
        $columnExpression = $columnData[ColumnResolverInterface::ALIAS];

        if ($columnData[ColumnAggregationTypeResolver::USE_AGGREGATION]) {
            $aggregationExpression = $this->aggregationType
                ->getParentAggregationExpression($scheme->getColumnById($columnId));
            $columnExpression = sprintf(
                $aggregationExpression,
                $columnExpression
            );
        }

        $relation = [
            RelationBuilder::TYPE => \Zend_Db_Select::LEFT_JOIN,
            RelationBuilder::ALIAS => $alias,
            RelationBuilder::TABLE => $content[self::SELECT],
            RelationBuilder::PARENT => $columnData[ColumnResolverInterface::ENTITY_NAME],
            RelationBuilder::CONDITION => sprintf(
                '%s.%s = %s.%s',
                $alias,
                $linkedFiled,
                $columnData[ColumnResolverInterface::ENTITY_NAME],
                $linkedFiled
            ),
            RelationBuilder::COLUMNS => [$columnData[ColumnResolverInterface::ALIAS] => $columnExpression],
            RelationBuilder::CONTENT => $content
        ];

        $this->relationStorage->addRelation($relation);

        return $relation;
    }

    private function getLinkedField(array $columnData): string
    {
        if (array_key_exists($columnData[ColumnResolverInterface::ENTITY_NAME], $this->entitiesWithRowId)) {
            $this->linkField = $this->metadataPool->getMetadata(
                $this->entitiesWithRowId[$columnData[ColumnResolverInterface::ENTITY_NAME]]
            )->getLinkField();
        }

        return $this->linkField;
    }

    private function getColumnTableName(string $columnId, array $columnData): string
    {
        $entityName = $columnData[ColumnResolverInterface::ENTITY_NAME];
        $entityScheme = $this->provider->getEntityScheme();
        $entity = $entityScheme->getEntityByName($entityName);
        $columnEntity = $entityScheme->getColumnById($columnId);
        $backendType = $columnEntity->getType();
        $isEavEntity = in_array($backendType, ['int', 'decimal'])
            && $entityName == self::PRODUCT_ENTITY_NAME;
        $table = $isEavEntity
            ? self::AMASTY_REPORT_BUILDER_EAV_INDEX_TABLE
            : $entity->getMainTable();

        return sprintf('%s_%s', $this->resourceConnection->getTableName($table), $backendType);
    }

    private function getIndexField(string $columnId, string $linkedField): string
    {
        $entityScheme = $this->provider->getEntityScheme();
        $column = $entityScheme->getColumnById($columnId);
        $entityName = $column->getEntityName();
        $backendType = $column->getType();
        $isEavEntity = in_array($backendType, ['int', 'decimal'])
            && $entityName == self::PRODUCT_ENTITY_NAME;
        return  $isEavEntity ? self::ENTITY_ID : $linkedField;
    }

    /**
     * @param EntityInterface|null $entity
     * @param string $linkedFiled
     * @param array $columnData
     * @param string $indexField
     * @param string $tableName
     * @return array
     */
    private function buildContent(
        EntityInterface $entity,
        string $linkedFiled,
        array $columnData,
        string $indexField,
        string $tableName
    ): array {
        $select = $this->selectFactory->create();
        $select->from(
            [
                $entity->getName() => $this->resourceConnection->getTableName($entity->getMainTable())
            ]
        );
        $select->reset(\Magento\Framework\DB\Select::COLUMNS);
        $select->columns(sprintf('%s.%s', $entity->getName(), $linkedFiled));

        $relations = $this->attributeRelationResolver->resolve($columnData, $linkedFiled, $indexField, $tableName);
        foreach ($relations as $relation) {
            $select->joinByType(
                $relation[RelationBuilder::TYPE],
                [$relation[RelationBuilder::ALIAS] => $relation[RelationBuilder::TABLE]],
                $relation[RelationBuilder::CONDITION]
            );
        }

        $columns = $this->columnExpressionResolver->resolve($columnData, $relations);
        $select->columns($columns);
        $select->group(sprintf('%s.%s', $entity->getName(), $linkedFiled));

        return [
            self::RELATIONS => $relations,
            self::SELECT => $select,
            self::COLUMNS => $columns
        ];
    }
}
