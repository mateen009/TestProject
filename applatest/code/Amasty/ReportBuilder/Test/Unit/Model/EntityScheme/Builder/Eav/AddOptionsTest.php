<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Test\Unit\Model\EntityScheme\Builder\Eav;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Api\EntityInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Builder\Eav\AddOptions;
use Amasty\ReportBuilder\Test\Unit\Traits;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * @see AddOptions
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class AddOptionsTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var AddOptions
     */
    private $model;

    /**
     * @covers AddOptions::execute
     * @dataProvider executeDataProvider
     */
    public function testExecute(AbstractAttribute $attribute, array $entity, array $result): void
    {
        $this->model = $this->getObjectManager()->getObject(AddOptions::class, []);

        $this->model->execute($attribute, $entity, 'test');

        $this->assertEquals($result, $entity);
    }

    /**
     * Data provider for execute test
     * @return array
     */
    public function executeDataProvider(): array
    {
        $attribute1 = $this->createMock(AbstractAttribute::class);
        $attribute2 = $this->createMock(AbstractAttribute::class);
        $attribute3 = $this->createMock(AbstractAttribute::class);
        $source = $this->createMock(AbstractSource::class);

        $attribute1->expects($this->any())->method('getFrontendInput')->willReturn('test');
        $attribute2->expects($this->any())->method('getFrontendInput')->willReturn('select');
        $attribute3->expects($this->any())->method('getFrontendInput')->willReturn('multiselect');
        $attribute1->expects($this->any())->method('getSourceModel')->willReturn(false);
        $attribute2->expects($this->any())->method('getSourceModel')->willReturn(true);
        $attribute3->expects($this->any())->method('getSourceModel')->willReturn(false);
        $attribute3->expects($this->any())->method('getSource')->willReturn($source);
        $source->expects($this->any())->method('getAllOptions')->willReturn([
            ['value' => 'value1', 'label' => 'option1'], ['value' => 'value2', 'label' => 'option2']
        ]);

        return [
            [$attribute1, ['test'], ['test']],
            [$attribute2, ['test'], ['test']],
            [
                $attribute3,
                ['testValue'],
                [
                    'testValue',
                    EntityInterface::COLUMNS => [
                        'test' => [
                            ColumnInterface::OPTIONS => ['value1' => 'option1', 'value2' => 'option2']
                        ]
                    ]
                ]
            ],
            [
                $attribute3,
                [
                    'testValue',
                    EntityInterface::COLUMNS => [
                        'test' => [
                            ColumnInterface::OPTIONS => ['value3' => 'option3']
                        ]
                    ]
                ],
                [
                    'testValue',
                    EntityInterface::COLUMNS => [
                        'test' => [
                            ColumnInterface::OPTIONS => [
                                'value1' => 'option1',
                                'value2' => 'option2',
                                'value3' => 'option3'
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }
}
