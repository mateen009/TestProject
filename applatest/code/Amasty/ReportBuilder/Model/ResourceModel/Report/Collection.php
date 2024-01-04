<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\ResourceModel\Report;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Model\ResourceModel\Report as ReportResource;
use Amasty\ReportBuilder\Model\Report as ReportModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = ReportInterface::REPORT_ID;

    public function _construct()
    {
        $this->_init(ReportModel::class, ReportResource::class);
    }
}
