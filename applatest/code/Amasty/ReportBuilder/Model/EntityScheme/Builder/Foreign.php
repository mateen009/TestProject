<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme\Builder;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Api\EntityInterface;
use Amasty\ReportBuilder\Api\EntityScheme\BuilderInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Column\ColumnType;
use Amasty\ReportBuilder\Model\EntityScheme\Column\DataType;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Declaration\Schema\Db\DbSchemaReaderInterface;

class Foreign implements BuilderInterface
{
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

    public function __construct(
        ResourceConnection $resourceConnection,
        DbSchemaReaderInterface $dbSchemaReader,
        DataType $dataType
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->dbSchemaReader = $dbSchemaReader;
        $this->dataType = $dataType;
    }

    /**
     * @param array $data
     * @return array
     * @throws LocalizedException
     */
    public function build(array $data = []): array
    {
        foreach ($data as $key => $entity) {
            foreach ($entity[EntityInterface::COLUMNS] as $columnName => $columnData) {
                if ($columnData[ColumnInterface::COLUMN_TYPE] === ColumnType::FOREIGN_TYPE) {
                    [$entityName, $column] = explode('.', $columnData[ColumnInterface::LINK]);
                    if (!isset($entity[EntityInterface::RELATIONS][$entityName])) {
                        throw new LocalizedException(
                            __('Relation for foreign link %1 does not exist', $columnData[ColumnInterface::LINK])
                        );
                    }

                    $columnsData = $this->dbSchemaReader->readColumns(
                        $this->resourceConnection->getTableName($data[$entityName][EntityInterface::MAIN_TABLE]),
                        ResourceConnection::DEFAULT_CONNECTION
                    );
                    if (isset($columnsData[$column])) {
                        // phpcs:ignore
                        $data[$key][EntityInterface::COLUMNS][$columnName] = array_merge(
                            $this->getColumnData($columnsData[$column]),
                            $entity[EntityInterface::COLUMNS][$columnName]
                        );
                    }
                    if (isset($data[$entityName][EntityInterface::COLUMNS][$column])) {
                        $data[$key][EntityInterface::COLUMNS][$columnName]
                            = $data[$key][EntityInterface::COLUMNS][$columnName]
                            + $data[$entityName][EntityInterface::COLUMNS][$column];
                    }
                }
            }
        }

        return $data;
    }

    private function getColumnData(array $columnData): array
    {
        $typesMap = $this->dataType->getTypesMap();
        $type = $columnData[ColumnInterface::TYPE];

        return [
            ColumnInterface::NAME => $columnData[ColumnInterface::NAME],
            ColumnInterface::TITLE => $this->getTitle($columnData),
            ColumnInterface::TYPE => $typesMap[$type] ?? $type
        ];
    }

    private function getTitle(array $columnData): string
    {
        return $columnData[Db::COLUMN_DATA_COMMENT]
            ?: ucwords(str_replace('_', ' ', $columnData[ColumnInterface::NAME]));
    }
}
