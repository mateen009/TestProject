<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Api\EntityScheme;

interface ProviderInterface
{
    /**
     * @return SchemeInterface
     */
    public function getEntityScheme(): SchemeInterface;

    /**
     * return void
     */
    public function clear(): void;
}
