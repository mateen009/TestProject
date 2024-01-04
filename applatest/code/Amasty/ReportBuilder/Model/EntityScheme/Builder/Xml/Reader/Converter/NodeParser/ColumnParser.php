<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme\Builder\Xml\Reader\Converter\NodeParser;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Column\ColumnType;
use Magento\Framework\Exception\LocalizedException;

class ColumnParser implements ParserInterface
{
    const COLUMN_TYPE_ATTRIBUTE = 'columnType';

    public function parse(\DOMNode $childNode): array
    {
        $output = [];
        foreach ($childNode->getElementsByTagName('column') as $columnNode) {
            $name = $columnNode->getAttribute(ColumnInterface::NAME);
            $columnType = $columnNode->getAttribute(self::COLUMN_TYPE_ATTRIBUTE);
            $output[$name] = [
                ColumnInterface::NAME => $name,
                ColumnInterface::COLUMN_TYPE => $columnType ?: ColumnType::DEFAULT_TYPE,
                ColumnInterface::USE_FOR_PERIOD => filter_var(
                    $columnNode->getAttribute(ColumnInterface::USE_FOR_PERIOD_ATTRIBUTE),
                    FILTER_VALIDATE_BOOLEAN
                ),
                ColumnInterface::FRONTEND_MODEL => $columnNode->getAttribute(ColumnInterface::FRONTEND_MODEL_ATTRIBUTE)
                    ?: '',
                ColumnInterface::PRIMARY => filter_var(
                    $columnNode->getAttribute(ColumnInterface::PRIMARY),
                    FILTER_VALIDATE_BOOLEAN
                ),
            ];

            foreach ($columnNode->childNodes as $item) {
                if ($item->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                if ($item->nodeName == 'options') {
                    $output[$name]['options'] = $this->parseOptions($item);
                } elseif ($item->nodeName === ColumnInterface::HIDDEN) {
                    $output[$name][ColumnInterface::HIDDEN] = filter_var(
                        $item->nodeValue,
                        FILTER_VALIDATE_BOOLEAN
                    );
                } else {
                    $output[$name][$item->nodeName] = $item->nodeValue;
                }
            }

            $this->validateByColumnType($output[$name]);
        }

        return $output;
    }

    private function parseOptions(\DOMNode $item): array
    {
        $options = [];
        foreach ($item->getElementsByTagName('option') as $optionNode) {
            $optionName = $optionNode->getAttribute('name');
            $options[$optionName] = $optionNode->nodeValue;
        }

        return $options;
    }

    /**
     * @param array $column
     * @return void
     * @throws LocalizedException
     */
    private function validateByColumnType(array $column): void
    {
        if ($column[ColumnInterface::COLUMN_TYPE] === ColumnType::FOREIGN_TYPE
            && !isset($column[ColumnInterface::LINK])
        ) {
            throw new LocalizedException(__('Node link required for columnType = foreign.'));
        }
    }
}
