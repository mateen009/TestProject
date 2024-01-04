<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\ResourceModel\Report\Data;

use Magento\Framework\DB\Select as DbSelect;

class Select extends DbSelect
{
    const DEFAULT_COLUMN_ALIAS = 'id';
    const TABLE_NAME = 0;
    const COLUMN_NAME = 1;
    const COLUMN_ALIAS = 2;

    public function getFirstColumnAlias(): string
    {
        $columns = $this->getPart(self::COLUMNS);
        return $columns[0][self::COLUMN_ALIAS] ?? self::DEFAULT_COLUMN_ALIAS;
    }

    public function joinByType($type, $name, $cond, $cols = [], $schema = null): Select
    {
        return $this->_join($type, $name, $cond, $cols, $schema);
    }
}
