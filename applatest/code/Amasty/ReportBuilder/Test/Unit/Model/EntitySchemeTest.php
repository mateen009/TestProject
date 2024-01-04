<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Test\Unit\Model;

use Amasty\ReportBuilder\Api\EntityInterface;
use Amasty\ReportBuilder\Model\EntityScheme;
use Amasty\ReportBuilder\Test\Unit\Traits;

/**
 * @see EntityScheme
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class EntitySchemeTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var EntityScheme
     */
    private $model;

    protected function setUp(): void
    {
        $this->model = $this->getObjectManager()->getObject(EntityScheme::class);
    }

    /**
     * @covers EntityScheme::getAllEntitiesOptionArray
     */
    public function testGetAllEntitiesOptionArray(): void
    {
        $this->assertEquals([], $this->model->getAllEntitiesOptionArray());
        $this->model->init(
            [
                'entity1' => [
                    EntityInterface::PRIMARY => true,
                    EntityInterface::TITLE => 'test1'
                ],
                'entity2' => [
                    EntityInterface::TITLE => 'test2'
                ],
            ]
        );
        $this->assertEquals(['entity1' => 'test1'], $this->model->getAllEntitiesOptionArray(true));
        $this->assertEquals(['entity1' => 'test1', 'entity2' => 'test2'], $this->model->getAllEntitiesOptionArray());
    }
}
