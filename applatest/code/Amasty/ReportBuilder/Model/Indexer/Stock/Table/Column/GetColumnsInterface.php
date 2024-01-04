<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Indexer\Stock\Table\Column;

interface GetColumnsInterface
{
    /**
     * Getting columns with config.
     *
     * @return array
     */
    public function execute(): array;
}
