<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Indexer\Eav\Actions;

use Amasty\ReportBuilder\Model\Indexer\Eav\IndexAdapter;
use Amasty\ReportBuilder\Model\ResourceModel\Indexer\Eav\AbstractType;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Query\BatchIteratorInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\BatchProviderInterface;
use Magento\Framework\DB\Query\Generator as QueryGenerator;

class Full
{
    const BATCH_SIZE = 1000;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var BatchProviderInterface
     */
    private $batchProvider;

    /**
     * @var QueryGenerator|null
     */
    private $batchQueryGenerator;

    /**
     * @var IndexAdapter
     */
    private $indexAdapter;

    /**
     * @var ActiveTableSwitcher
     */
    private $activeTableSwitcher;

    public function __construct(
        MetadataPool $metadataPool,
        BatchProviderInterface $batchProvider,
        QueryGenerator $batchQueryGenerator,
        IndexAdapter $indexAdapter,
        ActiveTableSwitcher $activeTableSwitcher
    ) {
        $this->metadataPool = $metadataPool;
        $this->batchProvider = $batchProvider;
        $this->batchQueryGenerator = $batchQueryGenerator;
        $this->indexAdapter = $indexAdapter;
        $this->activeTableSwitcher = $activeTableSwitcher;
    }

    public function execute(): void
    {
        try {
            foreach ($this->indexAdapter->getIndexers() as $indexerName => $indexer) {
                $connection = $indexer->getConnection();
                $mainTable = $this->activeTableSwitcher->getAdditionalTableName($indexer->getMainTable());
                $connection->truncateTable($mainTable);

                foreach ($this->getBatches($connection, $indexerName, $indexer) as $query) {
                    $entityIds = $connection->fetchCol($query);
                    if (!empty($entityIds)) {
                        $indexer->reindexEntities($this->indexAdapter->processRelations($indexer, $entityIds, true));
                        $this->indexAdapter->syncData($indexer, null, $mainTable);
                    }
                }
                $this->activeTableSwitcher->switchTable($connection, [$indexer->getMainTable()]);

                $connection->truncateTable($indexer->getIdxTable());
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }

    private function getBatches(
        AdapterInterface $connection,
        string $indexerName,
        AbstractType $indexer
    ): BatchIteratorInterface {
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $select = $connection->select();
        $select->distinct(true);
        $select->from(
            ['e' => $indexer->getTable(sprintf('catalog_product_entity_%s', $indexerName))],
            $linkField
        );

        return $this->batchQueryGenerator->generate(
            $linkField,
            $select,
            self::BATCH_SIZE,
            BatchIteratorInterface::NON_UNIQUE_FIELD_ITERATOR
        );
    }
}
