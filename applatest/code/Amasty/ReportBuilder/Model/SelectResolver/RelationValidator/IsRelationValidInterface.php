<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\RelationValidator;

use Amasty\ReportBuilder\Exception\NotExistColumnException;
use Amasty\ReportBuilder\Exception\NotExistTableException;

interface IsRelationValidInterface
{
    /**
     * @param array $relation
     * @return void
     * @throws NotExistColumnException
     * @throws NotExistTableException
     */
    public function execute(array $relation): void;
}
