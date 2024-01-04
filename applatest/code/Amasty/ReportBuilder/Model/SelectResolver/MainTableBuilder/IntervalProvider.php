<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\MainTableBuilder;

use Amasty\ReportBuilder\Model\Source\IntervalType;

class IntervalProvider
{
    public function getInterval(string $fieldName, ?string $interval = null): array
    {
        switch ($interval) {
            case IntervalType::TYPE_YEAR:
                $expression = $group = sprintf('YEAR(DATE(%s))', $fieldName);
                break;
            case IntervalType::TYPE_MONTH:
                $expression = 'ADDDATE(DATE(DATE(%1$s)), INTERVAL 1-DAYOFMONTH(DATE(%1$s)) DAY)';
                $expression = sprintf($expression, $fieldName);
                $group = sprintf('MONTH(DATE(%s))', $fieldName);
                break;
            case IntervalType::TYPE_WEEK:
                $expression = 'ADDDATE(DATE(DATE(%1$s)), INTERVAL 1-DAYOFWEEK(DATE(%1$s)) DAY)';
                $expression = sprintf($expression, $fieldName);
                $group = sprintf('WEEK(DATE(%s))', $fieldName);
                break;
            case IntervalType::TYPE_DAY:
            default:
                $expression = $group = sprintf('DATE(DATE(%s))', $fieldName);
                break;
        }

        return [$expression, $group];
    }
}
