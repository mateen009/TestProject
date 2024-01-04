<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\RelationValidator;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Provider as SchemeProvider;
use Amasty\ReportBuilder\Model\ResourceModel\Table\IsColumnExist as IsColumnExistResource;
use Amasty\ReportBuilder\Model\ResourceModel\Table\IsTableExist as IsTableExistResource;
use Amasty\ReportBuilder\Exception\NotExistColumnException;
use Amasty\ReportBuilder\Exception\NotExistTableException;

class IsSimpleRelationValid implements IsRelationValidInterface
{
    /**
     * @var SchemeProvider
     */
    private $schemeProvider;

    /**
     * @var IsTableExistResource
     */
    private $isTableExistResource;

    /**
     * @var IsColumnExistResource
     */
    private $isColumnExistResource;

    public function __construct(
        SchemeProvider $schemeProvider,
        IsTableExistResource $isTableExistResource,
        IsColumnExistResource $isColumnExistResource
    ) {
        $this->schemeProvider = $schemeProvider;
        $this->isTableExistResource = $isTableExistResource;
        $this->isColumnExistResource = $isColumnExistResource;
    }

    /**
     * @param array $relation
     * @return void
     * @throws NotExistColumnException
     * @throws NotExistTableException
     */
    public function execute(array $relation): void
    {
        $entityScheme = $this->schemeProvider->getEntityScheme();

        $sourceEntity = $entityScheme->getEntityByName($relation[ReportInterface::SCHEME_SOURCE_ENTITY]);
        $relatedEntity = $entityScheme->getEntityByName($relation[ReportInterface::SCHEME_ENTITY]);
        $relationScheme = $sourceEntity->getRelation($relation[ReportInterface::SCHEME_ENTITY]);

        if (!$this->isTableExistResource->execute($relatedEntity->getMainTable())) {
            throw new NotExistTableException(__('Table \'%1\' does not exist.', $relatedEntity->getMainTable()));
        }

        if (!$this->isColumnExistResource->execute(
            $relatedEntity->getMainTable(),
            $relationScheme->getReferenceColumn()
        )) {
            throw new NotExistColumnException(__(
                'Column \'%1\' does not exist for table \'%2\'',
                $relationScheme->getReferenceColumn(),
                $relatedEntity->getMainTable()
            ));
        }
    }
}
