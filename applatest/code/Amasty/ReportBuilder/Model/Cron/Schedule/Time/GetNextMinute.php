<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Cron\Schedule\Time;

use Magento\Framework\Stdlib\DateTime\DateTime;

class GetNextMinute
{
    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    public function execute(): string
    {
        return strftime('%Y-%m-%d %H:%M', $this->dateTime->gmtTimestamp('+1 minute'));
    }
}
