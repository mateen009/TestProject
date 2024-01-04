<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Test\Unit\Model\Backend\Report\DataCollector\Columns;

use Amasty\ReportBuilder\Model\Backend\Report\DataCollector\Columns\FilterCollector;
use Amasty\ReportBuilder\Test\Unit\Traits;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * @see FilterCollector
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class FilterCollectorTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var FilterCollector
     */
    private $model;

    /**
     * @covers FilterCollector::collectFilter
     */
    public function testCollectFilter(): void
    {
        $serializer = $this->getObjectManager()->getObject(Json::class);

        $this->model = $this->getObjectManager()->getObject(
            FilterCollector::class,
            [
                'serializer' => $serializer,
            ]
        );

        $this->assertEquals('', $this->model->collectFilter(['test']));

        $this->assertEquals('{"value":5}', $this->model->collectFilter([
            FilterCollector::COLUMN_DATA_FILTER => [
                FilterCollector::COLUMN_DATA_FILTER_IS_ACTIVE => true,
                FilterCollector::COLUMN_DATA_FILTER_VALUE => 5
            ]
        ]));

        $this->assertEquals(
            '{"from":5,"to":6}',
            $this->model->collectFilter([
                FilterCollector::COLUMN_DATA_FILTER => [
                    FilterCollector::COLUMN_DATA_FILTER_IS_ACTIVE => true,
                    FilterCollector::COLUMN_DATA_FILTER_VALUE => ['from' => 5, 'to' => 6]
                ]
            ])
        );
    }
}
