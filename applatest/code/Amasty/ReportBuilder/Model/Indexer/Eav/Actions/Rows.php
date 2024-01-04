<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Indexer\Eav\Actions;

use Amasty\ReportBuilder\Model\Indexer\Eav\IndexAdapter;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;

class Rows
{
    /**
     * @var IndexAdapter
     */
    private $indexAdapter;

    public function __construct(IndexAdapter $indexAdapter)
    {
        $this->indexAdapter = $indexAdapter;
    }

    public function execute(array $ids = null): void
    {
        if (empty($ids)) {
            throw new InputException(__('Bad value was supplied.'));
        }
        try {
            foreach ($this->indexAdapter->getIndexers() as $indexer) {
                $ids = $this->indexAdapter->processRelations($indexer, $ids);
                $indexer->reindexEntities($ids);
                $this->indexAdapter->syncData($indexer, $ids);
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }
}
