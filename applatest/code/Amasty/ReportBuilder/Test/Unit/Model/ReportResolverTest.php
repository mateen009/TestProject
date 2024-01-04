<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Test\Unit\Model;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Api\ReportRepositoryInterface;
use Amasty\ReportBuilder\Model\ReportRegistry;
use Amasty\ReportBuilder\Model\ReportResolver;
use Amasty\ReportBuilder\Test\Unit\Traits;

/**
 * @see ReportResolver
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class ReportResolverTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var ReportResolver
     */
    private $model;

    /**
     * @covers ReportResolver::resolve
     * @dataProvider resolveDataProvider
     */
    public function testResolve(?int $reportId, int $savedReportId, int $resultId): void
    {
        $reportRepository = $this->createMock(ReportRepositoryInterface::class);
        $registry = $this->createMock(ReportRegistry::class);
        $report = $this->createMock(ReportInterface::class);
        $reportTest = $this->createMock(ReportInterface::class);

        $report->expects($this->any())->method('getReportId')->willReturn($savedReportId);
        $reportTest->expects($this->any())->method('getReportId')->willReturn(20);
        $registry->expects($this->any())->method('getReport')->willReturn($report);
        $reportRepository->expects($this->any())->method('getById')->willReturn($reportTest);

        $this->model = $this->getObjectManager()->getObject(
            ReportResolver::class,
            [
                'repository' => $reportRepository,
                'registry' => $registry,
            ]
        );

        $resultReport = $this->model->resolve($reportId);

        $this->assertEquals($resultId, $resultReport->getReportId());
    }

    /**
     * Data provider for resolve test
     * @return array
     */
    public function resolveDataProvider(): array
    {
        return [
            [null, 1, 1],
            [1, 0, 20],
            [1, 2, 20],
            [2, 2, 2],
        ];
    }
}
