<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Product;

class CompositeTypes
{
    /**
     * @var string[]
     */
    private $types;

    public function __construct(array $types = [])
    {
        $this->types = $types;
    }

    public function get(): array
    {
        return $this->types;
    }
}
