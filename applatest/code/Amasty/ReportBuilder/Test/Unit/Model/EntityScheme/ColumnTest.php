<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Test\Unit\Model\EntityScheme;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Column;
use Amasty\ReportBuilder\Test\Unit\Traits;

/**
 * @see ReportResolver
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class ColumnTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var Column
     */
    private $model;

    /**
     * @covers Column::init
     * @dataProvider initDataProvider
     */
    public function testInit(array $config, string $frontendModel): void
    {
        $this->model = $this->getObjectManager()->getObject(Column::class, []);

        $this->model->init($config);

        $this->assertEquals($frontendModel, $this->model->getFrontendModel());
    }

    /**
     * Data provider for init test
     * @return array
     */
    public function initDataProvider(): array
    {
        return [
            [
                [ColumnInterface::NAME => 'name'],
                'text'
            ],
            [
                [
                    ColumnInterface::NAME => 'name',
                    ColumnInterface::OPTIONS => ['test']
                ],
                'select'
            ],
            [
                [
                    ColumnInterface::NAME => 'name',
                    ColumnInterface::SOURCE_MODEL => 'test'
                ],
                'select'
            ],
            [
                [
                    ColumnInterface::NAME => 'name',
                    ColumnInterface::TYPE => 'date'
                ],
                'dateRange'
            ],
            [
                [
                    ColumnInterface::NAME => 'name',
                    ColumnInterface::TYPE => 'datetime'
                ],
                'dateRange'
            ],
            [
                [
                    ColumnInterface::NAME => 'name',
                    'frontend_input' => 'timestamp'
                ],
                'dateRange'
            ],
            [
                [
                    ColumnInterface::NAME => 'name',
                    ColumnInterface::PRIMARY => true
                ],
                'textRange'
            ],
            [
                [
                    ColumnInterface::NAME => 'name',
                    'backend_type' => 'decimal'
                ],
                'textRange'
            ]
        ];
    }
}
