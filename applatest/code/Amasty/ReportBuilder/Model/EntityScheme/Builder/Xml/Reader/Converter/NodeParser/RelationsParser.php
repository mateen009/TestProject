<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme\Builder\Xml\Reader\Converter\NodeParser;

use Amasty\ReportBuilder\Api\RelationInterface;

class RelationsParser implements ParserInterface
{
    public function parse(\DOMNode $childNode): array
    {
        $output = [];
        foreach ($childNode->getElementsByTagName('relation') as $relationNode) {
            $name = $relationNode->getAttribute(RelationInterface::NAME);
            $output[$name] = [
                RelationInterface::NAME => $name,
                RelationInterface::TYPE => $relationNode->getAttribute('type'),
            ];

            foreach ($relationNode->childNodes as $item) {
                if ($item->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                $output[$name][$item->nodeName] = $item->nodeValue;
            }
        }

        return $output;
    }
}
