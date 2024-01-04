<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver;

use Amasty\ReportBuilder\Model\ResourceModel\Report\Data\Select;

interface MainTableBuilderInterface
{
    /**
     * Method builds main select
     *
     * @param string|null $interval
     * @return Select
     */
    public function build(?string $interval = null): Select;
}
