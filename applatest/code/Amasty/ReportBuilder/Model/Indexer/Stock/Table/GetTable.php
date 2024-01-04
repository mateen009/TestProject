<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Indexer\Stock\Table;

use Amasty\ReportBuilder\Model\Indexer\Stock\Table\Column\GetColumnsInterface;
use Amasty\ReportBuilder\Model\ResourceModel\Indexer\Stock\IndexResource;
use InvalidArgumentException;
use Magento\Framework\DB\Ddl\Table;
use Zend_Db_Exception;

class GetTable
{
    /**
     * @var IndexResource
     */
    private $indexResource;

    /**
     * @var GetColumnsInterface[]
     */
    private $columnResolvers;

    public function __construct(IndexResource $indexResource, array $columnResolvers = [])
    {
        $this->indexResource = $indexResource;
        $this->setColumnResolvers($columnResolvers);
    }

    /**
     * @param string $tableName
     * @return Table
     * @throws Zend_Db_Exception
     */
    public function execute(string $tableName): Table
    {
        $table = $this->indexResource->getNewTable($tableName);
        foreach ($this->columnResolvers as $columnResolver) {
            foreach ($columnResolver->execute() as $columnName => $columnData) {
                $table->addColumn($columnName, ...$columnData);
            }
        }

        return $table;
    }

    /**
     * @param GetColumnsInterface[] $columnResolvers
     * @return void
     * @throws InvalidArgumentException
     */
    private function setColumnResolvers(array $columnResolvers): void
    {
        foreach ($columnResolvers as $columnResolver) {
            if (!$columnResolver instanceof GetColumnsInterface) {
                throw new InvalidArgumentException(
                    sprintf('Column resolver must implement %s', GetColumnsInterface::class)
                );
            }
        }
        $this->columnResolvers = $columnResolvers;
    }
}
