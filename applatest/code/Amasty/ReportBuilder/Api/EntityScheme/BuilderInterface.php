<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Api\EntityScheme;

interface BuilderInterface
{
    /**
     * @param array $data
     * @return array
     */
    public function build(array $data = []): array;
}
