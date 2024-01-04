<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver;

use Amasty\ReportBuilder\Model\EntityScheme\Provider as SchemeProvider;
use Amasty\ReportBuilder\Model\ResourceModel\Report\Data\Select;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnBuilder\BuilderInterface;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnBuilder\RelationHelper;

class ColumnBuilder implements ColumnBuilderInterface
{
    /**
     * @var ColumnResolverInterface
     */
    private $columnResolver;

    /**
     * @var SchemeProvider
     */
    private $schemeProvider;

    /**
     * @var RelationHelper
     */
    private $relationHelper;

    /**
     * @var BuilderInterface[]
     */
    private $pool;

    public function __construct(
        Context $context,
        RelationHelper $relationHelper,
        array $pool = []
    ) {
        $this->columnResolver = $context->getColumnResolver();
        $this->schemeProvider = $context->getEntitySchemeProvider();
        $this->relationHelper = $relationHelper;
        $this->pool = $pool;
    }

    public function build(Select $select): void
    {
        $columns = $this->columnResolver->resolve();
        $scheme = $this->schemeProvider->getEntityScheme();

        foreach ($columns as $columnId => $columnData) {
            $column = $scheme->getColumnById($columnId);
            if (!$this->relationHelper->isColumnInSelect($select, $column)) {
                $columnBuilder = $this->pool[$column->getColumnType()] ?? $this->pool['default'];
                $columnBuilder->build($select, $columnId, $columnData);
            }
        }
    }
}
