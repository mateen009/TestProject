<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver;

use Amasty\ReportBuilder\Exception\NotExistColumnException;
use Amasty\ReportBuilder\Exception\NotExistTableException;

interface RelationValidatorInterface
{
    /**
     * @param array $relations
     * @return void
     * @throws NotExistColumnException
     * @throws NotExistTableException
     */
    public function execute(array $relations): void;
}
