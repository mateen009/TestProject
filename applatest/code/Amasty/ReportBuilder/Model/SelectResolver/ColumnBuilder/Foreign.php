<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnBuilder;

use Amasty\ReportBuilder\Model\EntityScheme\Provider as SchemeProvider;
use Amasty\ReportBuilder\Model\ResourceModel\Report\Data\Select;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnBuilder\Simple\AddColumnToSelect;
use Amasty\ReportBuilder\Model\SelectResolver\Context;

class Foreign implements BuilderInterface
{
    /**
     * @var SchemeProvider
     */
    private $schemeProvider;

    /**
     * @var AddColumnToSelect
     */
    private $addColumnToSelect;

    public function __construct(
        Context $context,
        AddColumnToSelect $addColumnToSelect
    ) {
        $this->schemeProvider = $context->getEntitySchemeProvider();
        $this->addColumnToSelect = $addColumnToSelect;
    }

    public function build(Select $select, string $columnId, array $columnData): void
    {
        $scheme = $this->schemeProvider->getEntityScheme();
        $column = $scheme->getColumnById($columnId);

        $this->addColumnToSelect->execute($select, $column->getParentColumn(), $columnId, $columnData);
    }
}
