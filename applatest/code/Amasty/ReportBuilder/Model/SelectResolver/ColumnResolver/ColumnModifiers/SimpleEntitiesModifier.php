<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnModifiers;

use Amasty\ReportBuilder\Model\EntityScheme\Provider;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolverInterface;

class SimpleEntitiesModifier implements ColumnModifierInterface
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
        $entityName = $column->getEntityName();

        return [
            ColumnResolverInterface::ALIAS => str_replace('.', '_', $columnId),
            ColumnResolverInterface::EXPRESSION => $columnId,
            ColumnResolverInterface::EXPRESSION_INTERNAL => $columnId,
            ColumnResolverInterface::ENTITY_NAME => $entityName
        ];
    }
}
