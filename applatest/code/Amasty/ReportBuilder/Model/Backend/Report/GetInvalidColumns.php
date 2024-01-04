<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Backend\Report;

use Amasty\ReportBuilder\Api\EntityScheme\ProviderInterface as SchemeProvider;
use Amasty\ReportBuilder\Model\View\ReportLoader;
use Magento\Framework\Exception\LocalizedException;

class GetInvalidColumns
{
    /**
     * @var ReportLoader
     */
    private $reportLoader;

    /**
     * @var SchemeProvider
     */
    private $schemeProvider;

    public function __construct(ReportLoader $reportLoader, SchemeProvider $schemeProvider)
    {
        $this->reportLoader = $reportLoader;
        $this->schemeProvider = $schemeProvider;
    }

    public function execute(bool $recollectScheme = false): array
    {
        if ($recollectScheme) {
            $this->schemeProvider->clear();
        }

        $entityScheme = $this->schemeProvider->getEntityScheme();
        $invalidColumns = [];
        foreach ($this->reportLoader->execute()->getAllColumns() as $columnId => $columnData) {
            try {
                $entityScheme->getColumnById($columnId);
            } catch (LocalizedException $e) {
                [$entityName, $columnName] = explode('.', $columnId);
                if (!isset($invalidColumns[$entityName])) {
                    $invalidColumns[$entityName] = [$columnName];
                } else {
                    $invalidColumns[$entityName][] = $columnName;
                }
            }
        }

        return $invalidColumns;
    }
}
