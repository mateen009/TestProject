<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Test\Unit\Model\EntityScheme;

use Amasty\ReportBuilder\Api\EntityInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Column;
use Amasty\ReportBuilder\Model\EntityScheme\ColumnFactory;
use Amasty\ReportBuilder\Model\EntityScheme\Entity;
use Amasty\ReportBuilder\Model\EntityScheme\Relation;
use Amasty\ReportBuilder\Model\EntityScheme\RelationFactory;
use Amasty\ReportBuilder\Test\Unit\Traits;
use Magento\Framework\Exception\LocalizedException;

/**
 * @see ReportResolver
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class EntityTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var Entity
     */
    private $model;

    /**
     * @var Column|\PHPUnit\Framework\MockObject\MockObject
     */
    private $column;

    /**
     * @var Relation|\PHPUnit\Framework\MockObject\MockObject
     */
    private $relation;

    protected function setUp(): void
    {
        $columnFactory = $this->createMock(ColumnFactory::class);
        $relationFactory = $this->createMock(RelationFactory::class);
        $this->column = $this->createMock(Column::class);
        $this->relation = $this->createMock(Relation::class);

        $columnFactory->expects($this->any())->method('create')->willReturn($this->column);
        $relationFactory->expects($this->any())->method('create')->willReturn($this->relation);

        $this->model = $this->getObjectManager()->getObject(
            Entity::class,
            [
                'columnFactory' => $columnFactory,
                'relationFactory' => $relationFactory,
            ]
        );
    }

    /**
     * @covers Entity::init
     */
    public function testInit(): void
    {
        $this->model->init([
            EntityInterface::NAME => 'name',
            EntityInterface::TITLE => 'title',
            EntityInterface::MAIN_TABLE => 'table',
            EntityInterface::COLUMNS => ['column' => [$this->column]],
            EntityInterface::RELATIONS => ['relation' => [$this->relation]],
            EntityInterface::EXPRESSIONS => ['expression_name' => 'expression']
        ]);

        $this->assertEquals('name', $this->model->getName());
        $this->assertEquals('title', $this->model->getTitle());
        $this->assertEquals('table', $this->model->getMainTable());
        $this->assertEquals($this->column, $this->model->getColumn('column'));
        $this->assertEquals($this->relation, $this->model->getRelation('relation'));
        $this->assertEquals(['expression_name' => 'expression'], $this->model->getExpressions());
    }

    /**
     * @covers Entity::init
     */
    public function testInitInvalid1()
    {
        $this->expectException(LocalizedException::class);
        $this->model->init([]);
    }

    /**
     * @covers Entity::init
     */
    public function testInitInvalid2()
    {
        $this->expectException(LocalizedException::class);
        $this->model->init([EntityInterface::NAME => 'name']);
    }

    /**
     * @covers Entity::init
     */
    public function testInitInvalid3()
    {
        $this->expectException(LocalizedException::class);
        $this->model->init([EntityInterface::NAME => 'name', EntityInterface::TITLE => 'title']);
    }

    /**
     * @covers Entity::init
     */
    public function testInitInvalid4()
    {
        $this->expectException(LocalizedException::class);
        $this->model->init([
            EntityInterface::NAME => 'name',
            EntityInterface::TITLE => 'title',
            EntityInterface::MAIN_TABLE => 'table'
        ]);
    }

    /**
     * @covers Entity::init
     */
    public function testInitInvalid5()
    {
        $this->expectException(LocalizedException::class);
        $this->model->init([
            EntityInterface::NAME => 'name',
            EntityInterface::TITLE => 'title',
            EntityInterface::MAIN_TABLE => 'table',
            EntityInterface::COLUMNS => [],
        ]);
    }
}
