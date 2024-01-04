<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Indexer;

use Amasty\ReportBuilder\Model\Indexer\Eav\Actions\Full;
use Amasty\ReportBuilder\Model\Indexer\Eav\Actions\Row;
use Amasty\ReportBuilder\Model\Indexer\Eav\Actions\Rows;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;

class Eav implements IndexerActionInterface, MviewActionInterface
{
    /**
     * @var Full
     */
    private $fullAction;

    /**
     * @var Row
     */
    private $rowAction;

    /**
     * @var Rows
     */
    private $rowsAction;

    public function __construct(
        Full $fullAction,
        Row $rowAction,
        Rows $rowsAction
    ) {
        $this->fullAction = $fullAction;
        $this->rowAction = $rowAction;
        $this->rowsAction = $rowsAction;
    }

    public function executeFull()
    {
        $this->fullAction->execute();
    }

    public function executeList(array $ids)
    {
        $this->rowsAction->execute($ids);
    }

    public function executeRow($id)
    {
        $this->rowAction->execute($id);
    }

    public function execute($ids)
    {
        $this->executeList($ids);
    }
}
