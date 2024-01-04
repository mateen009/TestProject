<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme;

use Amasty\ReportBuilder\Api\EntityScheme\BuilderInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Builder\Xml;

class Builder
{
    const DEFAULT_BUILDER_ORDER = 100;

    /**
     * @var Xml
     */
    private $xmlBuilder;

    /**
     * @var BuilderInterface[]
     */
    private $pool = [];

    public function __construct(
        Xml $xmlBuilder,
        array $builderPool = []
    ) {
        $this->xmlBuilder = $xmlBuilder;
        $this->pool = $builderPool;
    }

    public function build(): array
    {
        $schemeData = $this->xmlBuilder->build();
        foreach ($this->pool as $builder) {
            if ($builder instanceof BuilderInterface) {
                $schemeData = $builder->build($schemeData);
            }
        }

        return $schemeData;
    }
}
