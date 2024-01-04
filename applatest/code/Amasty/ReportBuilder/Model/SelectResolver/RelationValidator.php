<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver;

use Amasty\ReportBuilder\Model\SelectResolver\RelationValidator\IsRelationValidInterface;
use Amasty\ReportBuilder\Exception\NotExistColumnException;
use Amasty\ReportBuilder\Exception\NotExistTableException;

class RelationValidator implements RelationValidatorInterface
{
    /**
     * @var IsRelationValidInterface
     */
    private $isRelationValid;

    public function __construct(IsRelationValidInterface $isRelationValid)
    {
        $this->isRelationValid = $isRelationValid;
    }

    /**
     * @param array $relations
     * @return void
     * @throws NotExistColumnException
     * @throws NotExistTableException
     */
    public function execute(array $relations): void
    {
        foreach ($relations as $relation) {
            $this->isRelationValid->execute($relation);
        }
    }
}
