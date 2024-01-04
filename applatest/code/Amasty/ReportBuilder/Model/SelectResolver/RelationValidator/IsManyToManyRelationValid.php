<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\RelationValidator;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Provider as SchemeProvider;
use Amasty\ReportBuilder\Model\ResourceModel\Table\IsColumnExist as IsColumnExistResource;
use Amasty\ReportBuilder\Model\ResourceModel\Table\IsTableExist as IsTableExistResource;
use Amasty\ReportBuilder\Exception\NotExistColumnException;
use Amasty\ReportBuilder\Exception\NotExistTableException;

class IsManyToManyRelationValid implements IsRelationValidInterface
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

    /**
     * @var IsRelationValidInterface
     */
    private $isSimpleRelationValid;

    public function __construct(
        IsRelationValidInterface $isSimpleRelationValid,
        SchemeProvider $schemeProvider,
        IsTableExistResource $isTableExistResource,
        IsColumnExistResource $isColumnExistResource
    ) {
        $this->schemeProvider = $schemeProvider;
        $this->isTableExistResource = $isTableExistResource;
        $this->isColumnExistResource = $isColumnExistResource;
        $this->isSimpleRelationValid = $isSimpleRelationValid;
    }

    public function execute(array $relation): void
    {
        $this->isSimpleRelationValid->execute($relation);

        $entityScheme = $this->schemeProvider->getEntityScheme();
        $sourceEntity = $entityScheme->getEntityByName($relation[ReportInterface::SCHEME_SOURCE_ENTITY]);
        $relationScheme = $sourceEntity->getRelation($relation[ReportInterface::SCHEME_ENTITY]);

        if (!$this->isTableExistResource->execute($relationScheme->getRelationTable())) {
            throw new NotExistTableException(__('Table \'%1\' does not exist.', $relationScheme->getRelationTable()));
        }

        if (!$this->isColumnExistResource->execute(
            $relationScheme->getRelationTable(),
            $relationScheme->getRelationReferenceColumn()
        )) {
            throw new NotExistColumnException(__(
                'Column \'%1\' does not exist for table \'%2\'',
                $relationScheme->getRelationReferenceColumn(),
                $relationScheme->getRelationTable()
            ));
        }
    }
}
