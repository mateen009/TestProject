<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme\Builder\Xml\Reader;

use Magento\Framework\Module\Dir;

class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * @var string
     */
    private $schema;

    /**
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     */
    public function __construct(\Magento\Framework\Module\Dir\Reader $moduleReader)
    {
        $directory = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Amasty_ReportBuilder');
        $this->schema =  $directory . '/entity_scheme.xsd';
    }

    /**
     * {@inheritdoc}
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * {@inheritdoc}
     */
    public function getPerFileSchema()
    {
        return $this->schema;
    }
}
