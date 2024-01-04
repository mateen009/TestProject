<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Test\Unit\Model\EntityScheme;

use Amasty\ReportBuilder\Api\RelationInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Relation;
use Amasty\ReportBuilder\Model\EntityScheme\Relation\Type;
use Amasty\ReportBuilder\Test\Unit\Traits;
use Magento\Framework\Exception\LocalizedException;

/**
 * @see Relation
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class RelationTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var Relation
     */
    private $model;

    protected function setUp(): void
    {
        $this->model = $this->getObjectManager()->getObject(Relation::class, []);
    }

    /**
     * @covers Relation::resolve
     */
    public function testResolve(): void
    {
        $this->model->init([
            RelationInterface::NAME => 'name',
            RelationInterface::COLUMN => 'column',
            RelationInterface::REFERENCE_COLUMN => 'ref'
        ]);

        $this->assertEquals(Type::TYPE_COLUMN, $this->model->getType());
    }

    /**
     * @covers Relation::resolve
     */
    public function testResolveInvalid1()
    {
        $this->expectException(LocalizedException::class);
        $this->model->init([]);
    }

    /**
     * @covers Relation::resolve
     */
    public function testResolveInvalid2()
    {
        $this->expectException(LocalizedException::class);
        $this->model->init([RelationInterface::NAME => 'name']);
    }

    /**
     * @covers Relation::resolve
     */
    public function testResolveInvalid3()
    {
        $this->expectException(LocalizedException::class);
        $this->model->init([RelationInterface::NAME => 'name', RelationInterface::COLUMN => 'column']);
    }
}
