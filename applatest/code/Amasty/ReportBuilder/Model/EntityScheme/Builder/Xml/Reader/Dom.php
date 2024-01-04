<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme\Builder\Xml\Reader;

use Amasty\ReportBuilder\Api\EntityInterface;
use Magento\Framework\Config\Dom as ParentDom;

class Dom extends ParentDom
{
    protected function _mergeNode(\DOMElement $node, $parentPath)
    {
        $path = $this->_getNodePathByParent($node, $parentPath);
        $matchedNodes = $this->getMatchedNodes($node, $path);
        if ($matchedNodes) {
            if ($node->hasChildNodes()) {
                foreach ($node->childNodes as $childNode) {
                    if ($childNode instanceof \DOMElement) {
                        $this->_mergeNode($childNode, $path);
                    }
                }
            } else {
                parent::_mergeNode($node, $parentPath);
            }
            $this->_mergeAttributes($matchedNodes[0], $node);
        } else {
            $parentMatchedNode = $this->_getMatchedNode($parentPath);
            $newNode = $this->dom->importNode($node, true);
            $parentMatchedNode->appendChild($newNode);
        }
    }

    private function getMatchedNodes(\DOMElement $node, string $nodePath): array
    {
        $xPath = new \DOMXPath($this->dom);
        $matchedNodes = $xPath->query($nodePath);

        $nodes = [];
        foreach ($matchedNodes as $matchedNode) {
            if ($matchedNode->getAttribute(EntityInterface::NAME) == $node->getAttribute(EntityInterface::NAME)) {
                $nodes[] = $matchedNode;
            }
        }

        return $nodes;
    }
}
