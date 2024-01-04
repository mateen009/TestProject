<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\ResourceModel\Indexer\Eav;

class IntType extends AbstractType
{
    protected function _construct()
    {
        $this->_init('amasty_report_builder_eav_index_int', 'entity_id');
    }

    protected function getSourceTable(): string
    {
        return $this->getTable('catalog_product_entity_int');
    }
}
