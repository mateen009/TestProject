<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Test\Unit\Model\Report;

use Amasty\ReportBuilder\Api\EntityInterface;
use Amasty\ReportBuilder\Api\EntityScheme\SchemeInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Provider as SchemeProvider;
use Amasty\ReportBuilder\Model\Report\EntityProvider;
use PHPUnit\Framework\TestCase;

class EntityProviderTest extends TestCase
{
    /**
     * @covers EntityProvider::getEntities
     *
     * @dataProvider getEntitiesDataProvider
     *
     * @param array $entitiesData
     * @param int $expectedResult
     */
    public function testGetEntities(array $entitiesData, int $expectedResult): void
    {
        $schemeProvider = $this->createMock(SchemeProvider::class);
        $entityMap = [];
        foreach ($entitiesData as $entityData) {
            $entity = $this->createMock(\Amasty\ReportBuilder\Model\EntityScheme\Entity::class);
            $entity->expects($this->any())->method('getName')->willReturn($entityData[EntityInterface::NAME]);
            $entity->expects($this->any())->method('getRelatedEntities')->willReturn([]);
            $entity->expects($this->any())->method('toArray')->willReturn([]);
            $entity->expects($this->any())->method('isHidden')->willReturn(
                $entityData[EntityInterface::HIDDEN]
            );
            $entityMap[] = [$entityData[EntityInterface::NAME], $entity];
        }

        $entityScheme = $this->createMock(SchemeInterface::class);
        $entityScheme->expects($this->any())->method('getEntityByName')->willReturnMap($entityMap);
        $schemeProvider->expects($this->any())->method('getEntityScheme')->willReturn($entityScheme);

        $model = new EntityProvider($schemeProvider);
        $entities = $model->getEntities(array_column($entitiesData, EntityInterface::NAME));
        $this->assertEquals($expectedResult, count($entities));
    }

    /**
     * @return array
     */
    public function getEntitiesDataProvider(): array
    {
        return [
            [
                [
                    [
                        EntityInterface::NAME => 'test_1',
                        EntityInterface::HIDDEN => false
                    ],
                    [
                        EntityInterface::NAME => 'test_2',
                        EntityInterface::HIDDEN => false
                    ]
                ],
                2
            ],
            [
                [
                    [
                        EntityInterface::NAME => 'test_1',
                        EntityInterface::HIDDEN => false
                    ],
                    [
                        EntityInterface::NAME => 'test_2',
                        EntityInterface::HIDDEN => true
                    ]
                ],
                1
            ]
        ];
    }
}
