<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\ResourceModel;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Api\RelationInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Report extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(ReportInterface::MAIN_TABLE, ReportInterface::REPORT_ID);
    }

    /**
     * @param ReportInterface $object
     * @return Report
     */
    protected function _afterLoad(AbstractModel $object)
    {
        $this->loadColumns($object);
        $this->loadRelationsScheme($object);

        return parent::_afterLoad($object);
    }

    private function loadColumns(ReportInterface $object): void
    {
        $select = $this->getSelectByReportId(ColumnInterface::COLUMN_TABLE, $object->getReportId());
        $columns = $this->getConnection()->fetchAssoc($select);
        $preparedColumns = [];
        foreach ($columns as $column) {
            $preparedColumns[$column[ReportInterface::COLUMN_ID]] = $column;
        }

        uasort($preparedColumns, function ($prev, $next): int {
            $prevPosition = $prev['position'] ?? 0;
            $nextPosition = $next['position'] ?? 0;

            return $prevPosition <=> $nextPosition;
        });

        $object->setColumns($preparedColumns);
    }

    private function loadRelationsScheme(ReportInterface $object): void
    {
        $select = $this->getSelectByReportId(RelationInterface::SCHEME_ROUTING_TABLE, $object->getReportId())
            ->order('scheme_id ASC');
        $object->setRelationScheme($this->getConnection()->fetchAll($select));
    }

    private function getSelectByReportId(string $tableName, int $reportId): \Magento\Framework\DB\Select
    {
        return $this->getConnection()->select()
            ->from($this->getTable($tableName))
            ->where('report_id = ?', $reportId);
    }

    /**
     * @param ReportInterface $object
     * @return Report
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(AbstractModel $object)
    {
        $storeIds = $object->getStoreIds();
        $object->setData(ReportInterface::STORE_IDS, implode(',', $storeIds));

        parent::save($object);

        $this->saveColumns($object);
        $this->saveRelations($object);

        return $this;
    }

    private function saveColumns(AbstractModel $report): void
    {
        $this->removeColumns($report);

        $columns = $this->prepareData($report, $report->getAllColumns());
        if ($columns) {
            $this->getConnection()->insertOnDuplicate(
                $this->getTable(ColumnInterface::COLUMN_TABLE),
                array_values($columns)
            );
        }
    }

    private function removeColumns(AbstractModel $report): void
    {
        $this->getConnection()->delete(
            $this->getTable(ColumnInterface::COLUMN_TABLE),
            sprintf('%s = %s', ReportInterface::REPORT_ID, $report->getReportId())
        );
    }

    private function saveRelations(AbstractModel $report): void
    {
        $this->removeRelations($report);
        $relations = $this->prepareData($report, $report->getRelationScheme());
        if ($relations) {
            $this->getConnection()->insertOnDuplicate(
                $this->getTable(RelationInterface::SCHEME_ROUTING_TABLE),
                $relations
            );
        }
    }

    private function removeRelations(AbstractModel $report): void
    {
        $this->getConnection()->delete(
            $this->getTable(RelationInterface::SCHEME_ROUTING_TABLE),
            sprintf('%s = %s', ReportInterface::REPORT_ID, $report->getReportId())
        );
    }

    private function prepareData(AbstractModel $report, array $data = []): array
    {
        if ($data) {
            foreach ($data as &$item) {
                $item[ReportInterface::REPORT_ID] = $report->getReportId();
            }
        }

        return $data;
    }
}
