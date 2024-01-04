<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Test\Unit\Model\SelectResolver\RelationModifiers;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Api\EntityInterface;
use Amasty\ReportBuilder\Api\EntityScheme\SchemeInterface;
use Amasty\ReportBuilder\Api\RelationInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Provider;
use Amasty\ReportBuilder\Model\EntityScheme\Relation\JoinType;
use Amasty\ReportBuilder\Model\EntityScheme\Relation\Type;
use Amasty\ReportBuilder\Model\ResourceModel\Report\Data\Select;
use Amasty\ReportBuilder\Model\SelectResolver\RelationModifiers\TypeTableModifier\CreateSubselect;
use Amasty\ReportBuilder\Model\SelectResolver\RelationResolver;
use Amasty\ReportBuilder\Test\Unit\Traits;
use Amasty\ReportBuilder\Model\SelectResolver\RelationModifiers\TypeTableModifier;

/**
 * @see TypeTableModifier
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class TypeTableModifierTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var TypeTableModifier
     */
    private $model;

    /**
     * @covers TypeTableModifier::modify
     */
    public function testModify(): void
    {
        $provider = $this->createMock(Provider::class);
        $createSubselect = $this->createMock(CreateSubselect::class);
        $entityScheme = $this->createMock(SchemeInterface::class);
        $sourceEntity = $this->createMock(EntityInterface::class);
        $relationSchemeValid = $this->createMock(RelationInterface::class);
        $relationSchemeInvalid = $this->createMock(RelationInterface::class);
        $select = $this->createMock(Select::class);
        $joinType = $this->createMock(JoinType::class);

        $createSubselect->expects($this->any())->method('execute')->willReturn($select);
        $provider->expects($this->any())->method('getEntityScheme')->willReturn($entityScheme);
        $entityScheme->expects($this->any())->method('getEntityByName')->willReturn($sourceEntity);
        $sourceEntity->expects($this->any())->method('getName')->willReturn('sourceName');
        $sourceEntity->expects($this->any())->method('getRelation')
            ->willReturnOnConsecutiveCalls($relationSchemeValid, $relationSchemeInvalid);
        $relationSchemeInvalid->expects($this->any())->method('getType')->willReturn('test');
        $relationSchemeInvalid->expects($this->any())->method('getRelationshipType')->willReturn('test');
        $relationSchemeValid->expects($this->any())->method('getType')->willReturn(Type::TYPE_TABLE);
        $relationSchemeValid->expects($this->any())->method('getJoinType')->willReturn(JoinType::INNER_JOIN);
        $joinType->expects($this->any())->method('getJoinForSelect')->willReturn(\Zend_Db_Select::INNER_JOIN);

        $this->model = $this->getObjectManager()->getObject(
            TypeTableModifier::class,
            [
                'provider' => $provider,
                'createSubselect' => $createSubselect,
                'joinType' => $joinType
            ]
        );

        $relations = [
            'relation1' => [
                ReportInterface::SCHEME_ENTITY => 'test1',
            ],
            'relation2' => [
                ReportInterface::SCHEME_SOURCE_ENTITY => 'source1',
                ReportInterface::SCHEME_ENTITY => 'test2'
            ],
            'relation3' => [
                ReportInterface::SCHEME_SOURCE_ENTITY => 'source2',
                ReportInterface::SCHEME_ENTITY => 'test3'
            ],
        ];

        $resultRelations = [
            'relation1' => [
                ReportInterface::SCHEME_ENTITY => 'test1',
            ],
            'relation2' => [
                RelationResolver::TYPE => \Zend_Db_Select::INNER_JOIN,
                RelationResolver::ALIAS => 'test2',
                RelationResolver::PARENT => 'sourceName',
                RelationResolver::EXPRESSION => $select,
            ],
            'relation3' => [
                ReportInterface::SCHEME_SOURCE_ENTITY => 'source2',
                ReportInterface::SCHEME_ENTITY => 'test3'
            ],
        ];

        $this->assertEquals($resultRelations, $this->model->modify($relations));
    }
}
