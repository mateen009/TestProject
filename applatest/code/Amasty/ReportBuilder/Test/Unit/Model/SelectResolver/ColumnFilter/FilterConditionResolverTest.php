<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Test\Unit\Model\SelectResolver\ColumnFilter;

use Amasty\ReportBuilder\Model\EntityScheme\Column\DataType;
use Amasty\ReportBuilder\Model\EntityScheme\Column\FilterConditionType;
use Amasty\ReportBuilder\Model\SelectResolver\ColumnFilter\FilterConditionResolver;
use Amasty\ReportBuilder\Test\Unit\Traits;

/**
 * @see FilterConditionResolver
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class FilterConditionResolverTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var FilterConditionResolver
     */
    private $model;

    /**
     * @covers FilterConditionResolver::resolve
     * @dataProvider resolveDataProvider
     */
    public function testResolve(string $columnType, array $condition, array $result): void
    {
        $this->model = $this->getObjectManager()->getObject(FilterConditionResolver::class, []);

        $this->assertEquals($result, $this->model->resolve($columnType, $condition));
    }

    /**
     * Data provider for resolve test
     * @return array
     */
    public function resolveDataProvider(): array
    {
        return [
            [
                DataType::DATE,
                [FilterConditionType::CONDITION_FROM => 1, FilterConditionType::CONDITION_TO => 2],
                ['gt' => 1, 'lt' => 2]
            ],
            [DataType::INTEGER, [FilterConditionType::CONDITION_VALUE => 20], ['eq' => 20]],
            [DataType::DECIMAL, [FilterConditionType::CONDITION_VALUE => 20], ['eq' => 20]],
            [DataType::TEXT, [FilterConditionType::CONDITION_VALUE => 'test'], ['like' => '%test%']],
            ['strict', ['test'], ['test']],
        ];
    }
}
