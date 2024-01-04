<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\ResourceModel\Report\Data;

use Amasty\ReportBuilder\Api\SelectResolverInterface;
use Amasty\ReportBuilder\Exception\CollectionFetchException;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Amasty\ReportBuilder\Model\ResourceModel\Report\Data as DataResource;
use Magento\Framework\DataObject;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Zend_Db_Exception;

class Collection extends \Magento\Framework\Data\Collection
{
    /**
     * @var SelectResolverInterface
     */
    private $selectResolver;

    /**
     * @var false|\Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var int
     */
    private $totalRecords;

    /**
     * @var array
     */
    private $itemsData;

    /**
     * @var Select
     */
    private $select;

    /**
     * @var FetchStrategyInterface
     */
    private $fetchStrategy;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SelectFactory
     */
    private $selectFactory;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        DataResource $resource,
        SelectResolverInterface $selectResolver,
        FetchStrategyInterface $fetchStrategy,
        LoggerInterface $logger,
        SelectFactory $selectFactory
    ) {
        parent::__construct($entityFactory);
        $this->selectResolver = $selectResolver;
        $this->connection = $resource->getConnection();
        $this->fetchStrategy = $fetchStrategy;
        $this->logger = $logger;
        $this->_itemObjectClass = DataObject::class;
        $this->selectFactory = $selectFactory;
    }

    public function setReportId(int $reportId): void
    {
        $this->selectResolver->setReportId($reportId);
    }

    public function setInterval(string $interval): void
    {
        $this->selectResolver->setInterval($interval);
    }

    public function getSelect(): Select
    {
        if ($this->select === null) {
            $this->select = $this->selectResolver->getSelect();
        }

        return $this->select;
    }

    public function getAllIds(): array
    {
        $select = $this->getCleanSelect();

        $select->columns($this->getIdFieldName(), 'main_table');
        return $this->connection->fetchCol($select);
    }

    public function getSize(): int
    {
        if ($this->totalRecords === null) {
            $select = $this->getSizeSelect();
            $this->totalRecords = $this->connection->fetchOne($select);
        }
        return (int)$this->totalRecords;
    }

    public function getData(): array
    {
        if ($this->itemsData === null) {
            if ($this->getPageSize()) {
                $this->getSelect()->limitPage($this->getCurPage(), $this->getPageSize());
            }
            try {
                $this->itemsData = $this->fetchStrategy->fetchAll($this->getSelect());
            } catch (Zend_Db_Exception $e) {
                $this->logger->error($e->getMessage());
                throw new CollectionFetchException(__('Something went wrong while report load.'));
            }
        }
        return $this->itemsData;
    }

    public function logQuery(bool $logQuery = false): void
    {
        if ($logQuery || $this->getFlag('log_query')) {
            $this->logger->info($this->getSelect()->__toString());
        }
    }

    public function getSizeSelect(): Select
    {
        $select = $this->getCleanSelect();

        $group = $this->getSelect()->getPart(\Magento\Framework\DB\Select::GROUP);
        if (!is_array($group) || !count($group)) {
            $select->columns(new \Zend_Db_Expr('COUNT(*)'));
            return $select;
        }

        $select->columns(new \Zend_Db_Expr(("COUNT(DISTINCT " . implode(", ", $group) . ")")));
        $outerSelect = $this->selectFactory->create();
        $outerSelect->from($select, 'COUNT(*)');
        return $outerSelect;
    }

    public function addOrder(string $field, string $direction = self::SORT_ORDER_DESC): void
    {
        $direction = strtoupper($direction) == Select::SQL_ASC ? Select::SQL_ASC : Select::SQL_DESC;

        $this->selectResolver->setOrder($field, $direction);
    }

    /**
     * @param string $field
     * @param string $direction
     * @return void
     */
    public function setOrder($field, $direction = Select::SQL_DESC)
    {
        $this->addOrder((string) $field, (string) $direction);
    }

    public function addFieldToFilter($field, $condition): void
    {
        $this->selectResolver->addFilter((string) $field, $condition);
    }

    /**
     * @param bool $printQuery
     * @param bool $logQuery
     * @return \Magento\Framework\Data\Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        $this->loadCollection((bool)$logQuery);

        return $this;
    }

    private function getCleanSelect(): Select
    {
        $select = clone $this->getSelect();
        $this->selectResolver->applyFilters($select);
        $select->reset(\Magento\Framework\DB\Select::ORDER);
        $select->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $select->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $select->reset(\Magento\Framework\DB\Select::COLUMNS);

        return $select;
    }

    private function loadCollection(bool $logQuery = false): void
    {
        if (!$this->isLoaded()) {
            $this->selectResolver->applyFilters($this->getSelect());
            if ($this->getPageSize()) {
                $this->getSelect()->limitPage($this->getCurPage(), $this->getPageSize());
            }

            $this->logQuery($logQuery);
            $data = $this->getData();
            if (is_array($data)) {
                foreach ($data as $row) {
                    $item = $this->getNewEmptyItem();
                    if ($this->getIdFieldName()) {
                        $item->setIdFieldName($this->getIdFieldName());
                    }
                    $item->addData($row);
                    $this->addItem($item);
                }
            }
            $this->_setIsLoaded();
        }
    }

    private function getIdFieldName(): string
    {
        return $this->getSelect()->getFirstColumnAlias();
    }

    public function reset(): void
    {
        $this->_setIsLoaded(false);
        $this->_items = [];
        $this->itemsData = null;
    }

    public function __clone()
    {
        if (is_object($this->select)) {
            $this->select = clone $this->select;
        }
    }
}
