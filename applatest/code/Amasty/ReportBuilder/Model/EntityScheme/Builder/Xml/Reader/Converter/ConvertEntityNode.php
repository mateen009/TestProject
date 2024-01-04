<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme\Builder\Xml\Reader\Converter;

use Amasty\ReportBuilder\Api\EntityInterface;

class ConvertEntityNode
{
    /**
     * @var NodeParser
     */
    private $nodeParser;

    public function __construct(
        NodeParser $nodeParser
    ) {
        $this->nodeParser = $nodeParser;
    }

    public function execute(\DOMNode $node): array
    {
        $data = [];
        $attributes = $node->attributes;
        $nameNode = $attributes->getNamedItem(EntityInterface::NAME);

        $name = $nameNode->nodeValue;
        $data[EntityInterface::NAME] = $nameNode->nodeValue;
        $eavNode = $attributes->getNamedItem(EntityInterface::EAV);
        if ($eavNode !== null) {
            $data[EntityInterface::EAV] = filter_var($eavNode->nodeValue, FILTER_VALIDATE_BOOLEAN);
        }

        $primary = $attributes->getNamedItem(EntityInterface::PRIMARY);
        if ($primary !== null) {
            $data[EntityInterface::PRIMARY] = filter_var($primary->nodeValue, FILTER_VALIDATE_BOOLEAN);
        }

        /** @var $childNode \DOMNode */
        foreach ($node->childNodes as $childNode) {
            if ($childNode->nodeType != XML_ELEMENT_NODE) {
                continue;
            }

            $nodeData = $this->nodeParser->parse($childNode);
            // phpcs:ignore
            $data = array_merge($data, $nodeData);
        }
        if (isset($data[EntityInterface::HIDDEN])) {
            $isHidden = filter_var($data[EntityInterface::HIDDEN], FILTER_VALIDATE_BOOLEAN);
            $data[EntityInterface::HIDDEN] = $isHidden;
            if ($isHidden) {
                $data[EntityInterface::PRIMARY] = false;
            }
        }

        return [$name => $data];
    }
}
