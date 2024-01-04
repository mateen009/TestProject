<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnModifiers;

use Amasty\ReportBuilder\Model\EntityScheme\Provider;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolverInterface;

class EavEntitiesModifier implements ColumnModifierInterface
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
        $schemeColumn = $this->provider->getEntityScheme()->getColumnById($columnId);

        $entityName = explode('.', $columnId)[0] ?? '';
        $alias = str_replace('.', '_', $columnId);

        return [
            ColumnResolverInterface::ALIAS => $alias,
            ColumnResolverInterface::EXPRESSION => $alias,
            ColumnResolverInterface::EXPRESSION_INTERNAL => $columnId,
            ColumnResolverInterface::ENTITY_NAME => $entityName,
            ColumnResolverInterface::ATTRIBUTE_ID => $schemeColumn->getAttributeId()
        ];
    }
}
