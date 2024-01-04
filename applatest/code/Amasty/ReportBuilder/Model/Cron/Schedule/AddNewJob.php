<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Cron\Schedule;

use Amasty\ReportBuilder\Model\Cron\Schedule\Time\GetNextMinute;
use Magento\Cron\Model\ResourceModel\Schedule as ScheduleResource;
use Magento\Cron\Model\Schedule;
use Magento\Cron\Model\ScheduleFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

class AddNewJob
{
    const EVERY_MINUTE_EXPR = '* * * * *';

    /**
     * @var ScheduleFactory
     */
    private $scheduleFactory;

    /**
     * @var ScheduleResource
     */
    private $scheduleResource;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var GetNextMinute
     */
    private $getNextMinute;

    public function __construct(
        ScheduleFactory $scheduleFactory,
        ScheduleResource $scheduleResource,
        DateTime $dateTime,
        GetNextMinute $getNextMinute
    ) {
        $this->scheduleFactory = $scheduleFactory;
        $this->scheduleResource = $scheduleResource;
        $this->dateTime = $dateTime;
        $this->getNextMinute = $getNextMinute;
    }

    /**
     * Add job for next minute.
     *
     * @param string $jobCode
     * @return void
     */
    public function execute(string $jobCode): void
    {
        $schedule = $this->scheduleFactory->create();
        $schedule->setCronExpr(self::EVERY_MINUTE_EXPR);
        $schedule->setJobCode($jobCode);
        $schedule->setStatus(Schedule::STATUS_PENDING);
        $schedule->setCreatedAt(strftime('%Y-%m-%d %H:%M:%S', $this->dateTime->gmtTimestamp()));
        $schedule->setScheduledAt($this->getNextMinute->execute());
        $this->scheduleResource->save($schedule);
    }
}
