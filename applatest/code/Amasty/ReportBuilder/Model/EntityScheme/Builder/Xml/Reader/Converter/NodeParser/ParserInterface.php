<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme\Builder\Xml\Reader\Converter\NodeParser;

interface ParserInterface
{
    public function parse(\DOMNode $childNode): array;
}
