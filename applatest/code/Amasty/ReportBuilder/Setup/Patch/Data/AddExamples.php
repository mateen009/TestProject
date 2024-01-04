<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Setup\Patch\Data;

use Amasty\ReportBuilder\Model\Template\ExampleReport;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddExamples implements DataPatchInterface
{
    const MODULE_NAME = 'Amasty_ReportBuilder';

    /**
     * @var ExampleReport
     */
    private $exampleReport;

    public function __construct(
        ExampleReport $exampleReport
    ) {
        $this->exampleReport = $exampleReport;
    }

    /**
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return AddExamples
     */
    public function apply()
    {
        $this->exampleReport->createExampleReports(self::MODULE_NAME);
        return $this;
    }
}
