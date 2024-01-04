<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme\Builder\Xml\Reader;

use Amasty\ReportBuilder\Model\EntityScheme\Builder\Xml\Reader\Converter\ConvertEntityNode;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    const ENTITY_XPATH = '/config/amasty_report_builder_entities/entity';

    /**
     * @var ConvertEntityNode
     */
    private $convertEntityNode;

    public function __construct(
        ConvertEntityNode $convertEntityNode
    ) {
        $this->convertEntityNode = $convertEntityNode;
    }

    /**
     * @inheritdoc
     *
     * @param \DOMDocument $source
     * @return array
     * @throws \Exception
     */
    public function convert($source)
    {
        $config = [];
        $xpath = new \DOMXPath($source);
        /** @var $resourceNode \DOMNode */
        foreach ($xpath->query(self::ENTITY_XPATH) as $node) {
            // phpcs:ignore
            $config = array_merge($config, $this->convertEntityNode->execute($node));
        }

        return $config;
    }
}
