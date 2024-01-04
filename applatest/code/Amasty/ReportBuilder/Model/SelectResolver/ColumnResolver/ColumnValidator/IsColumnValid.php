<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnValidator;

use Amasty\ReportBuilder\Model\EntityScheme\Provider as SchemeProvider;
use Amasty\ReportBuilder\Exception\NotExistColumnException;

class IsColumnValid implements IsColumnValidInterface
{
    /**
     * @var SchemeProvider
     */
    private $schemeProvider;

    /**
     * @var IsColumnValidInterface
     */
    private $isColumnValidDefault;

    /**
     * @var IsColumnValidInterface[]
     */
    private $validatorPool;

    public function __construct(
        SchemeProvider $schemeProvider,
        IsColumnValidInterface $isColumnValidDefault,
        array $validatorPool = []
    ) {
        $this->schemeProvider = $schemeProvider;
        $this->isColumnValidDefault = $isColumnValidDefault;
        $this->validatorPool = $validatorPool;
    }

    /**
     * @param string $columnId
     * @return void
     * @throws NotExistColumnException
     */
    public function execute(string $columnId): void
    {
        $column = $this->schemeProvider->getEntityScheme()->getColumnById($columnId);
        $columnValidator = $this->validatorPool[$column->getColumnType()] ?? $this->isColumnValidDefault;
        $columnValidator->execute($columnId);
    }
}
