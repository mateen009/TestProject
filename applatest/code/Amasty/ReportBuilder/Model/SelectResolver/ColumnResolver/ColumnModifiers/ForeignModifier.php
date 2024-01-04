<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnModifiers;

use Amasty\ReportBuilder\Model\EntityScheme\Provider;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolverInterface;

class ForeignModifier implements ColumnModifierInterface
{
    /**
     * @var Provider
     */
    private $provider;

    public function __construct(Provider $provider)
    {
        $this->provider = $provider;
    }

    public function modify(string $columnId, array $columnData): array
    {
        $column = $this->provider->getEntityScheme()->getColumnById($columnId);
        $entityName = $column->getParentColumn()->getEntityName();

        return [
            ColumnResolverInterface::ALIAS => str_replace('.', '_', $columnId),
            ColumnResolverInterface::EXPRESSION => $column->getParentColumn()->getColumnId(),
            ColumnResolverInterface::EXPRESSION_INTERNAL => $column->getParentColumn()->getColumnId(),
            ColumnResolverInterface::ENTITY_NAME => $entityName
        ];
    }
}
