<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Test\Unit\Model\Backend\Report\DataCollector;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Model\Backend\Report\DataCollector\Columns;
use Amasty\ReportBuilder\Model\Backend\Report\DataCollector\Columns\FilterCollector;
use Amasty\ReportBuilder\Test\Unit\Traits;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * @see Columns
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class ColumnsTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var Columns
     */
    private $model;

    /**
     * @covers Columns::collect
     */
    public function testCollect(): void
    {
        $serializer = $this->createMock(Json::class);
        $filterCollector = $this->createMock(FilterCollector::class);
        $report = $this->createMock(ReportInterface::class);

        $serializer->expects($this->any())->method('unserialize')->willReturn([
            [
                Columns::COLUMN_DATA_ID => 1,
                Columns::COLUMN_DATA_DATE_FILTER => 1,
                Columns::COLUMN_DATA_ORDER => 5,
                Columns::COLUMN_DATA_VISIBILITY => 0,
                Columns::COLUMN_DATA_POSITION => 3,
                Columns::COLUMN_DATA_CUSTOM_TITLE => ''
            ]
        ]);
        $filterCollector->expects($this->any())->method('collectFilter')->willReturn('filter');

        $this->model = $this->getObjectManager()->getObject(
            Columns::class,
            [
                'serializer' => $serializer,
                'filterCollector' => $filterCollector,
            ]
        );

        $this->assertEquals([], $this->model->collect($report, []));

        $inputData = [
            Columns::COLUMNS_DATA_KEY => 'key'
        ];

        $result = [
            ReportInterface::COLUMNS => [
                1 => [
                    ReportInterface::COLUMN_ID => 1,
                    ColumnInterface::IS_DATE_FILTER => 1,
                    ColumnInterface::ORDER => 5,
                    ColumnInterface::FILTER => 'filter',
                    ColumnInterface::VISIBILITY => 0,
                    ColumnInterface::POSITION => 3,
                    ColumnInterface::AGGREGATION_TYPE => '',
                    ColumnInterface::CUSTOM_TITLE => ''
                ]
            ]
        ];

        $this->assertEquals($result, $this->model->collect($report, $inputData));
    }
}
