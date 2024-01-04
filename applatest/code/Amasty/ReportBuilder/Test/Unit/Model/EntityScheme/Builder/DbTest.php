<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Test\Unit\Model\EntityScheme\Builder;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Api\EntityInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Builder\Db;
use Amasty\ReportBuilder\Test\Unit\Traits;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Declaration\Schema\Db\DbSchemaReaderInterface;

/**
 * @see Db
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class DbTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var Db
     */
    private $model;

    /**
     * @covers Db::build
     */
    public function testBuild(): void
    {
        $resourceConnection = $this->createMock(ResourceConnection::class);
        $dbSchemaReader = $this->createMock(DbSchemaReaderInterface::class);

        $dbSchemaReader->expects($this->any())->method('readColumns')->willReturn([
            'column1' => [
                ColumnInterface::NAME => 'name1',
                DB::COLUMN_DATA_COMMENT => 'comment1',
                ColumnInterface::TYPE => 'type1'
            ],
            'column2' => [
                ColumnInterface::NAME => 'name2',
                DB::COLUMN_DATA_COMMENT => '',
                ColumnInterface::TYPE => 'type2'
            ]
        ]);

        $this->model = $this->getObjectManager()->getObject(
            Db::class,
            [
                'resourceConnection' => $resourceConnection,
                'dbSchemaReader' => $dbSchemaReader,
            ]
        );

        $this->assertEquals($this->getResultData(), $this->model->build([
            [EntityInterface::MAIN_TABLE => 'entity1', EntityInterface::COLUMNS => ['column1' => []]],
            [EntityInterface::MAIN_TABLE => 'entity2', EntityInterface::COLUMNS => ['test' => []]]
        ]));
    }

    private function getResultData(): array
    {
        return [
            [
                EntityInterface::COLUMNS => [
                    'column1' => [
                        'name' => 'name1',
                        'title' => 'comment1',
                        'type' => 'type1',
                        'column_type' => 'default'
                    ],
                    'column2' => [
                        'name' => 'name2',
                        'title' => 'Name2',
                        'type' => 'type2',
                        'column_type' => 'default'
                    ],
                ],
                'main_table' => 'entity1'
            ],
            [
                EntityInterface::COLUMNS => [
                    'test' => [],
                    'column1' => [
                        'name' => 'name1',
                        'title' => 'comment1',
                        'type' => 'type1',
                        'column_type' => 'default'
                    ],
                    'column2' => [
                        'name' => 'name2',
                        'title' => 'Name2',
                        'type' => 'type2',
                        'column_type' => 'default'
                    ]
                ],
                'main_table' => 'entity2'
            ]
        ];
    }
}
