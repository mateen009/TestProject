<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Test\Unit\Model\EntityScheme\Column;

use Amasty\ReportBuilder\Model\EntityScheme\Column\AggregationType;
use Amasty\ReportBuilder\Test\Unit\Traits;

/**
 * @see AggregationType
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class AggregationTypeTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var AggregationType
     */
    private $model;

    /**
     * @covers AggregationType::checkTypeExpression
     */
    public function testCheckTypeExpression(): void
    {
        $this->model = $this->getObjectManager()->getObject(AggregationType::class, []);

        $this->assertTrue($this->model->checkTypeExpression('%s', AggregationType::TYPE_NONE));
        $this->assertFalse($this->model->checkTypeExpression('%s', AggregationType::TYPE_SUM));
    }
}
