<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme\Builder;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Api\EntityInterface;
use Amasty\ReportBuilder\Api\EntityScheme\BuilderInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Builder\Column\TypeModifierInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Column\ColumnType;
use Amasty\ReportBuilder\Model\EntityScheme\Column\DataType;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Declaration\Schema\Db\DbSchemaReaderInterface;

class Db implements BuilderInterface
{
    const COLUMN_DATA_COMMENT = 'comment';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var DbSchemaReaderInterface
     */
    private $dbSchemaReader;

    /**
     * @var DataType
     */
    private $dataType;

    /**
     * @var TypeModifierInterface[]
     */
    private $columnModifiers;

    public function __construct(
        ResourceConnection $resourceConnection,
        DbSchemaReaderInterface $dbSchemaReader,
        DataType $dataType,
        array $columnModifiers = []
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->dbSchemaReader = $dbSchemaReader;
        $this->dataType = $dataType;
        $this->columnModifiers = $columnModifiers;
    }

    public function build(array $data = []): array
    {
        foreach ($data as &$entity) {
            $tableInfo = $this->dbSchemaReader->readColumns(
                $this->resourceConnection->getTableName($entity[EntityInterface::MAIN_TABLE]),
                ResourceConnection::DEFAULT_CONNECTION
            );

            $this->modifyColumns($tableInfo, $entity);
        }

        return $data;
    }

    private function modifyColumns(array $tableInfo, array &$entity): void
    {
        foreach ($tableInfo as $key => $column) {
            $columnData = $this->getColumnData($column);

            if (isset($entity[EntityInterface::COLUMNS]) && isset($entity[EntityInterface::COLUMNS][$key])) {
                // phpcs:ignore;
                $entity[EntityInterface::COLUMNS][$key] = array_merge(
                    $columnData,
                    $entity[EntityInterface::COLUMNS][$key]
                );
            } else {
                $entity[EntityInterface::COLUMNS][$key] = $columnData;
            }
        }
    }

    private function getColumnData(array $columnData): array
    {
        $typesMap = $this->dataType->getTypesMap();
        $type = $columnData[ColumnInterface::TYPE];

        $data = [
            ColumnInterface::NAME => $columnData[ColumnInterface::NAME],
            ColumnInterface::TITLE => $this->getTitle($columnData),
            ColumnInterface::TYPE => $typesMap[$type] ?? $type,
            ColumnInterface::COLUMN_TYPE => ColumnType::DEFAULT_TYPE
        ];

        $columnModifier = $this->columnModifiers[$type] ?? null;
        if ($columnModifier && $columnModifier instanceof TypeModifierInterface) {
            $data = $columnModifier->execute($data);
        }

        return $data;
    }

    private function getTitle(array $columnData): string
    {
        return $columnData[self::COLUMN_DATA_COMMENT]
            ?: ucwords(str_replace('_', ' ', $columnData[ColumnInterface::NAME]));
    }
}
