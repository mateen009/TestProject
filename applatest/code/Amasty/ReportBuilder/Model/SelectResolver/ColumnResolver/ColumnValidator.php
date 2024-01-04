<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver;

use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnValidator\IsColumnValidInterface;

class ColumnValidator implements ColumnValidatorInterface
{
    /**
     * @var IsColumnValidInterface
     */
    private $isColumnValid;

    public function __construct(IsColumnValidInterface $isColumnValid)
    {
        $this->isColumnValid = $isColumnValid;
    }

    public function execute(array $columns): void
    {
        foreach ($columns as $columnId => $columnData) {
            $this->isColumnValid->execute($columnId);
        }
    }
}
