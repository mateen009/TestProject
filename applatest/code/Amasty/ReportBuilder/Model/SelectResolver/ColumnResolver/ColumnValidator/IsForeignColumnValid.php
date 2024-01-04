<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnValidator;

use Amasty\ReportBuilder\Model\EntityScheme\Provider as SchemeProvider;
use Amasty\ReportBuilder\Model\ResourceModel\Table\IsColumnExist;
use Amasty\ReportBuilder\Exception\NotExistColumnException;

class IsForeignColumnValid implements IsColumnValidInterface
{
    /**
     * @var SchemeProvider
     */
    private $schemeProvider;

    /**
     * @var IsColumnExist
     */
    private $isColumnExist;

    public function __construct(
        SchemeProvider $schemeProvider,
        IsColumnExist $isColumnExist
    ) {
        $this->schemeProvider = $schemeProvider;
        $this->isColumnExist = $isColumnExist;
    }

    /**
     * @param string $columnId
     * @return void
     * @throws NotExistColumnException
     */
    public function execute(string $columnId): void
    {
        $entityScheme = $this->schemeProvider->getEntityScheme();
        $column = $entityScheme->getColumnById($columnId)->getParentColumn();
        $entity = $entityScheme->getEntityByName($column->getEntityName());

        if (!$this->isColumnExist->execute($entity->getMainTable(), $column->getName())) {
            throw new NotExistColumnException(__(
                'Column \'%1\' does not exist for table \'%2\'',
                $column->getName(),
                $entity->getMainTable()
            ));
        }
    }
}
