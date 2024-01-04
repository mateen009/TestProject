<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Test\Unit\Model\Backend\Report\DataCollector;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Model\Backend\Report\DataCollector\General;
use Amasty\ReportBuilder\Test\Unit\Traits;
use Magento\Framework\Exception\LocalizedException;

/**
 * @see General
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class GeneralTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var General
     */
    private $model;

    /**
     * @var ReportInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $report;

    protected function setUp(): void
    {
        $this->report = $this->createMock(ReportInterface::class);
        $this->model = $this->getObjectManager()->getObject(General::class, []);
    }

    /**
     * @covers General::collect
     */
    public function testResolve(): void
    {
        $inputData = [
            ReportInterface::MAIN_ENTITY => 'entity',
            ReportInterface::REPORT_ID => 1,
            ReportInterface::STORE_IDS => null,
            ReportInterface::NAME => 'name',
            ReportInterface::USE_PERIOD => 'false',
            ReportInterface::CHART_AXIS_X => 'x',
            ReportInterface::CHART_AXIS_Y => 'y',
            ReportInterface::SCHEME_ENTITY => 'test',
            ReportInterface::DISPLAY_CHART => 'true'
        ];

        $result = [
            ReportInterface::REPORT_ID => 1,
            ReportInterface::STORE_IDS => [0],
            ReportInterface::NAME => 'name',
            ReportInterface::USE_PERIOD => false,
            ReportInterface::CHART_AXIS_X => 'x',
            ReportInterface::CHART_AXIS_Y => 'y',
            ReportInterface::MAIN_ENTITY => 'entity',
            ReportInterface::DISPLAY_CHART => true
        ];

        $this->assertEquals($result, $this->model->collect($this->report, $inputData));
    }

    /**
     * @covers General::collect
     */
    public function testResolveInvalid(): void
    {
        $this->expectException(LocalizedException::class);

        $this->assertEquals([], $this->model->collect($this->report, []));
    }
}
