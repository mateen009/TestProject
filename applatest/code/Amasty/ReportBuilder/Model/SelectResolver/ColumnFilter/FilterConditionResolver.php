<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnFilter;

use Amasty\ReportBuilder\Model\EntityScheme\Column\DataType;
use Amasty\ReportBuilder\Model\EntityScheme\Column\FilterConditionType;

class FilterConditionResolver implements FilterConditionResolverInterface
{
    public function resolve(string $columnType, array $condition): array
    {
        switch ($columnType) {
            case DataType::DATE:
            case DataType::DATETIME:
                $conditions = $this->resolveFromToConditions($condition);
                break;
            case DataType::INTEGER:
            case DataType::DECIMAL:
                $conditions = $this->resolveDecimalConditions($condition);
                break;
            case DataType::VARCHAR:
            case DataType::TEXT:
                $conditions = $this->resolveTextCondiotns($condition);
                break;
            default:
                $conditions = $condition;
        }

        return $conditions;
    }

    private function resolveFromToConditions(array $condition): array
    {
        $conditions = [];

        if (isset($condition[FilterConditionType::CONDITION_FROM])) {
            $conditions['gt'] = $condition[FilterConditionType::CONDITION_FROM];
        }

        if (isset($condition[FilterConditionType::CONDITION_TO])) {
            $conditions['lt'] = $condition[FilterConditionType::CONDITION_TO];
        }

        return $conditions;
    }

    private function resolveDecimalConditions(array $condition): array
    {
        $conditions = $this->resolveFromToConditions($condition);

        if (empty($conditions) && isset($condition[FilterConditionType::CONDITION_VALUE])) {
            $conditions['eq'] = $condition[FilterConditionType::CONDITION_VALUE];
        }

        return $conditions;
    }

    private function resolveTextCondiotns(array $condition): array
    {
        $conditions = [];

        if (isset($condition[FilterConditionType::CONDITION_VALUE])) {
            $conditions['like'] = '%' . $condition[FilterConditionType::CONDITION_VALUE] . '%';
        }

        return $conditions;
    }
}
