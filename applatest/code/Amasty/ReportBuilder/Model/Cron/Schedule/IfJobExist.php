<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Cron\Schedule;

use Amasty\ReportBuilder\Model\Cron\Schedule\Time\GetNextMinute;
use Magento\Cron\Model\ResourceModel\Schedule\Collection as ScheduleCollection;
use Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory as ScheduleCollectionFactory;

class IfJobExist
{
    /**
     * @var ScheduleCollectionFactory
     */
    private $scheduleCollectionFactory;

    /**
     * @var GetNextMinute
     */
    private $getNextMinute;

    public function __construct(ScheduleCollectionFactory $scheduleCollectionFactory, GetNextMinute $getNextMinute)
    {
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        $this->getNextMinute = $getNextMinute;
    }

    /**
     * Check if job exist scheduled at next minute.
     *
     * @param string $jobCode
     * @return bool
     */
    public function execute(string $jobCode): bool
    {
        /** @var ScheduleCollection $scheduleCollection */
        $scheduleCollection = $this->scheduleCollectionFactory->create();
        $scheduleCollection->addFieldToFilter('job_code', $jobCode);
        $scheduleCollection->addFieldToFilter('scheduled_at', $this->getNextMinute->execute());
        $scheduleCollection->setPageSize(1);

        return (bool) $scheduleCollection->getSize();
    }
}
