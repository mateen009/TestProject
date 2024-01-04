<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Indexer\Eav;

use Amasty\ReportBuilder\Model\ResourceModel\Indexer\Eav\AbstractType;
use Amasty\ReportBuilder\Model\ResourceModel\Indexer\Eav\DecimalTypeFactory;
use Amasty\ReportBuilder\Model\ResourceModel\Indexer\Eav\IntTypeFactory;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\AbstractEav;
use Magento\Framework\Exception\LocalizedException;

class IndexAdapter
{
    /**
     * @var DecimalTypeFactory
     */
    private $decimalType;

    /**
     * @var IntTypeFactory
     */
    private $intType;

    /**
     * @var array
     */
    private $types;

    public function __construct(
        DecimalTypeFactory $decimalType,
        IntTypeFactory $intType
    ) {
        $this->decimalType = $decimalType;
        $this->intType = $intType;
    }

    public function getIndexers(): array
    {
        if ($this->types === null) {
            $this->types = [
                'int' => $this->intType->create(),
                'decimal' => $this->decimalType->create(),
            ];
        }

        return $this->types;
    }

    public function processRelations(AbstractEav $indexer, array $ids, bool $onlyParents = false): array
    {
        $parentIds = $indexer->getRelationsByChild($ids);
        $parentIds = array_unique(array_merge($parentIds, $ids));
        $childIds = $onlyParents ? [] : $indexer->getRelationsByParent($parentIds);

        return array_unique(array_merge($ids, $childIds, $parentIds));
    }

    /**
     * @param AbstractType $indexer
     * @param null $ids
     * @param string $destinationTable
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function syncData(AbstractType $indexer, $ids = null, $destinationTable = ''): void
    {
        $connection = $indexer->getConnection();
        $connection->beginTransaction();
        try {
            $destinationTable = $destinationTable ?: $indexer->getMainTable();
            if (!empty($ids)) {
                $where = $connection->quoteInto('entity_id IN(?)', $ids);
                $connection->delete($destinationTable, $where);
            }

            $indexer->insertFromTable($indexer->getIdxTable(), $destinationTable);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }
}
