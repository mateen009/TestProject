<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Validation;

class ReportFailedFlag
{
    /**
     * @var bool
     */
    private $flag = false;

    public function set(): void
    {
        $this->flag = true;
    }

    public function get(): bool
    {
        return $this->flag;
    }
}
