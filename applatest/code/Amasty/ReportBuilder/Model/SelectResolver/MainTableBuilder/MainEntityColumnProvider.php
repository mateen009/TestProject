<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\MainTableBuilder;

use Amasty\ReportBuilder\Model\EntityScheme\Column\ColumnType;
use Amasty\ReportBuilder\Model\EntityScheme\Provider;
use Amasty\ReportBuilder\Model\ReportResolver;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnExpressionResolverInterface;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolverInterface;
use Amasty\ReportBuilder\Model\SelectResolver\Context;

class MainEntityColumnProvider
{
    /**
     * @var IntervalProvider
     */
    private $intervalProvider;

    /**
     * @var ColumnResolverInterface
     */
    private $columnResolver;

    /**
     * @var ColumnExpressionResolverInterface
     */
    private $columnExpressionResolver;

    /**
     * @var Provider
     */
    private $provider;

    /**
     * @var ReportResolver
     */
    private $reportResolver;

    public function __construct(
        Context $context,
        IntervalProvider $intervalProvider
    ) {
        $this->intervalProvider = $intervalProvider;
        $this->columnResolver = $context->getColumnResolver();
        $this->columnExpressionResolver = $context->getColumnExpressionResolver();
        $this->provider = $context->getEntitySchemeProvider();
        $this->reportResolver = $context->getReportResolver();
    }

    public function getColumns(?string $interval = null): array
    {
        $columns = [];
        $report = $this->reportResolver->resolve();

        $columnName = $this->getColumnName();
        $alias = $report->getMainEntity() . '_' . $columnName;

        if ($report->getUsePeriod() && $interval) {
            [$expression, $group] = $this->intervalProvider->getInterval(
                sprintf('%s.%s', $report->getMainEntity(), $columnName),
                $interval
            );
            $columns[$alias] = new \Zend_Db_Expr($expression);
        } else {
            $expression = sprintf('%s.%s', $report->getMainEntity(), $columnName);
            $columns[$alias] = $expression;
            $group = $expression;
        }

        return [$this->getAllColumns($columns), $group];
    }

    private function getColumnName(): string
    {
        $report = $this->reportResolver->resolve();
        $scheme = $this->provider->getEntityScheme();
        $entity = $scheme->getEntityByName($report->getMainEntity());

        return $report->getUsePeriod() ? $entity->getPeriodColumn()->getName() : $entity->getPrimaryColumn()->getName();
    }

    private function getAllColumns(array $columns): array
    {
        $reportColumns = $this->columnResolver->resolve();
        $scheme = $this->provider->getEntityScheme();
        $report = $this->reportResolver->resolve();

        foreach ($reportColumns as $columnId => $columnData) {
            $column = $scheme->getColumnById($columnId);

            if ($column->getEntityName() == $report->getMainEntity()
                && !isset($columns[$columnData[ColumnResolverInterface::ALIAS]])
                && !in_array($column->getColumnType(), [ColumnType::EAV_TYPE, ColumnType::FOREIGN_TYPE])
            ) {
                $columns[$columnData[ColumnResolverInterface::ALIAS]] = $this->columnExpressionResolver->resolve(
                    $columnData[ColumnResolverInterface::ALIAS],
                    true
                );
            }
        }

        return $columns;
    }
}
