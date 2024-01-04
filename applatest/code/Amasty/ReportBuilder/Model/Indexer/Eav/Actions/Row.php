<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Indexer\Eav\Actions;

use Amasty\ReportBuilder\Model\Indexer\Eav\IndexAdapter;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;

class Row
{
    /**
     * @var IndexAdapter
     */
    private $indexAdapter;

    public function __construct(IndexAdapter $indexAdapter)
    {
        $this->indexAdapter = $indexAdapter;
    }

    public function execute(int $id): void
    {
        if (!isset($id) || empty($id)) {
            throw new InputException(__('We can\'t rebuild the index for an undefined product.'));
        }
        try {
            foreach ($this->indexAdapter->getIndexers() as $indexer) {
                $ids = $this->indexAdapter->processRelations($indexer, [$id]);
                $indexer->reindexEntities($ids);
                $this->indexAdapter->syncData($indexer, $ids);
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }
}
