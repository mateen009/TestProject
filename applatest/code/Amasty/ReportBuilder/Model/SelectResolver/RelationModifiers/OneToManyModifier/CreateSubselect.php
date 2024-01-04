<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\RelationModifiers\OneToManyModifier;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Api\EntityScheme\SchemeInterface;
use Amasty\ReportBuilder\Model\ResourceModel\Report\Data\Select;
use Amasty\ReportBuilder\Model\ResourceModel\Report\Data\SelectFactory;
use Magento\Framework\App\ResourceConnection;

class CreateSubselect
{
    /**
     * @var ResourceConnection
     */
    private $connectionResource;

    /**
     * @var SelectFactory
     */
    private $selectFactory;

    public function __construct(
        ResourceConnection $connectionResource,
        SelectFactory $selectFactory
    ) {
        $this->connectionResource = $connectionResource;
        $this->selectFactory = $selectFactory;
    }

    public function execute(SchemeInterface $entityScheme, array $relation): Select
    {
        $relatedEntity = $entityScheme->getEntityByName($relation[ReportInterface::SCHEME_ENTITY]);

        $select = $this->selectFactory->create();
        $select->from([
            $relatedEntity->getName() => $this->connectionResource->getTableName($relatedEntity->getMainTable())
        ]);

        foreach ($relatedEntity->getExpressions() as $expression) {
            $select->where($expression);
        }

        return $select;
    }
}
