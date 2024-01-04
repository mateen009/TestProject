<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Test\Unit\Model;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Model\Report;
use Amasty\ReportBuilder\Test\Unit\Traits;

/**
 * @see Report
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class ReportTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var Report
     */
    private $model;

    /**
     * @covers Report::getAllFilters
     */
    public function testGetAllFilters(): void
    {
        $this->model = $this->getObjectManager()->getObject(Report::class, []);
        $this->assertEquals([], $this->model->getAllFilters());

        $this->model->setColumns(
            [
                [
                    ReportInterface::REPORT_COLUMN_ID => 1,
                    ReportInterface::REPORT_COLUMN_FILTER => 'test'
                ],
                [
                    ReportInterface::REPORT_COLUMN_ID => 2,
                ],
                [
                    ReportInterface::REPORT_COLUMN_FILTER => 'test'
                ],
                [
                    ReportInterface::REPORT_COLUMN_FILTER => null
                ],
                [
                    ReportInterface::REPORT_COLUMN_ID => null
                ]
            ]
        );

        $this->assertEquals([1 => 'test'], $this->model->getAllFilters());
    }
}
