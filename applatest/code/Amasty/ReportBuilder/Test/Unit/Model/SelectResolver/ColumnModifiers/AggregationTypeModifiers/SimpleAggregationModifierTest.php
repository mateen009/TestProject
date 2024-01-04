<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Test\Unit\Model\SelectResolver\ColumnModifiers\AggregationTypeModifiers;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Api\EntityScheme\SchemeInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Column\AggregationType;
use Amasty\ReportBuilder\Model\EntityScheme\Provider;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnAggregationTypeResolver;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnModifiers\AggregationTypeModifiers\SimpleAggregationModifier;
use Amasty\ReportBuilder\Test\Unit\Traits;

/**
 * @see SimpleAggregationModifier
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class SimpleAggregationModifierTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var SimpleAggregationModifier
     */
    private $model;

    /**
     * @covers SimpleAggregationModifier::modify
     */
    public function testModify(): void
    {
        $provider = $this->createMock(Provider::class);
        $aggregationType = $this->createMock(AggregationType::class);
        $scheme = $this->createMock(SchemeInterface::class);
        $schemeColumn = $this->createMock(ColumnInterface::class);

        $aggregationType->expects($this->any())->method('getSimpleAggregationsType')
            ->willReturn([AggregationType::TYPE_MIN => 'MIN(%s)', AggregationType::TYPE_SUM => 'SUM(%s)']);
        $provider->expects($this->any())->method('getEntityScheme')->willReturn($scheme);
        $scheme->expects($this->any())->method('getColumnById')->willReturn($schemeColumn);
        $schemeColumn->expects($this->any())->method('getAggregationType')
            ->willReturn('test', AggregationType::TYPE_SUM);

        $this->model = $this->getObjectManager()->getObject(
            SimpleAggregationModifier::class,
            [
                'provider' => $provider,
                'aggregationType' => $aggregationType,
            ]
        );

        $columns = [
            'column1' => [

            ],
            'column2' => [

            ]
        ];

        $columnsResult = [
            'column1' => [

            ],
            'column2' => [
                ColumnAggregationTypeResolver::AGGREGATED_EXPRESSION => 'SUM(%s)'
            ]
        ];

        foreach ($columns as $columnId => $columnData) {
            $this->assertEquals($columnsResult[$columnId], $this->model->modify($columnId, $columnData));
        }
    }
}
