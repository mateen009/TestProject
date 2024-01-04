<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme\Builder\Xml\Reader\Converter\NodeParser;

class ExpressionsParser implements ParserInterface
{
    public function parse(\DOMNode $childNode): array
    {
        $output = [];
        foreach ($childNode->getElementsByTagName('expression') as $expressionNode) {
            $name = $expressionNode->getAttribute('name');
            $output[$name] = $expressionNode->nodeValue;
        }

        return $output;
    }
}
