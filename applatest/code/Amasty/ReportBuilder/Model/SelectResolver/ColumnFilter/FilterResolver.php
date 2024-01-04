<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnFilter;

use Amasty\Base\Model\Serializer;
use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Api\EntityScheme\ProviderInterface;
use Amasty\ReportBuilder\Model\ReportResolver;

class FilterResolver implements FilterResolverInterface
{
    /**
     * @var FilterStorageInterface
     */
    private $storage;

    /**
     * @var FilterConditionResolver
     */
    private $conditionResolver;

    /**
     * @var ReportResolver
     */
    private $reportResolver;

    /**
     * @var ProviderInterface
     */
    private $schemeProvider;

    private $serializer;

    public function __construct(
        FilterStorageInterface $storage,
        FilterConditionResolver $conditionResolver,
        ReportResolver $reportResolver,
        ProviderInterface $schemeProvider,
        Serializer $serializer
    ) {
        $this->storage = $storage;
        $this->conditionResolver = $conditionResolver;
        $this->reportResolver = $reportResolver;
        $this->schemeProvider = $schemeProvider;
        $this->serializer = $serializer;
    }

    public function resolve(): ?array
    {
        $filters = $this->storage->getAllFilters();
        $scheme = $this->schemeProvider->getEntityScheme();
        if (empty($filters)) {
            $report = $this->reportResolver->resolve();
            $reportColumns = $report->getAllColumns();

            foreach ($report->getAllFilters() as $columnId => $filterValue) {
                $columnData = $reportColumns[$columnId];
                if (!$columnData[ColumnInterface::VISIBILITY]) {
                    $column = $scheme->getColumnById($columnId);
                    if (!is_array($filterValue)) {
                        $filterValue = $this->serializer->unserialize($filterValue);
                    }
                    $condition = $this->conditionResolver->resolve($column->getType(), $filterValue);
                    $this->storage->addFilter($columnId, $condition);
                }
            }
        }

        return $this->storage->getAllFilters();
    }
}
