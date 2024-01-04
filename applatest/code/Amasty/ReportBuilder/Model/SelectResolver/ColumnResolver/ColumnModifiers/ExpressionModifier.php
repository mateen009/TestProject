<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnModifiers;

use Amasty\ReportBuilder\Model\EntityScheme\Provider;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnModifiers\ExpressionModifiers\ModifierInterface;

class ExpressionModifier
{
    /**
     * @var Provider
     */
    private $provider;

    /**
     * @var ModifierInterface[]
     */
    private $customModifiers;

    /**
     * @var ModifierInterface[]
     */
    private $expressionModifiers;

    public function __construct(
        Provider $provider,
        array $customModifiers = [],
        array $expressionModifiers = []
    ) {
        $this->provider = $provider;
        $this->customModifiers = $customModifiers;
        $this->expressionModifiers = $expressionModifiers;
    }

    /**
     * @param string $columnId
     * @param array $selectColumnData
     * @return array
     */
    public function modify(string $columnId, array $selectColumnData): array
    {
        $column = $this->provider->getEntityScheme()->getColumnById($columnId);

        $customModifier = $this->customModifiers[$column->getCustomExpression()] ?? null;
        if ($customModifier) {
            $selectColumnData = $customModifier->modify($columnId, $selectColumnData);
        }

        foreach ($this->expressionModifiers as $expressionModifier) {
            $selectColumnData = $expressionModifier->modify($columnId, $selectColumnData);
        }

        return $selectColumnData;
    }
}
