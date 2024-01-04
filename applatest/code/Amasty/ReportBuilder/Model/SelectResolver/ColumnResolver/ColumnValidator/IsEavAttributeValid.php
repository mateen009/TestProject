<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnResolver\ColumnValidator;

use Amasty\ReportBuilder\Model\EntityScheme\Provider as SchemeProvider;
use Amasty\ReportBuilder\Exception\NotExistColumnException;
use Magento\Eav\Model\Config as EavConfig;

class IsEavAttributeValid implements IsColumnValidInterface
{
    /**
     * @var SchemeProvider
     */
    private $schemeProvider;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    public function __construct(SchemeProvider $schemeProvider, EavConfig $eavConfig)
    {
        $this->schemeProvider = $schemeProvider;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @param string $columnId
     * @return void
     * @throws NotExistColumnException
     */
    public function execute(string $columnId): void
    {
        $column = $this->schemeProvider->getEntityScheme()->getColumnById($columnId);
        $attributesForEntity = $this->eavConfig->getEntityAttributes($column->getEntityName());
        if (!array_key_exists($column->getName(), $attributesForEntity)) {
            throw new NotExistColumnException(__(
                'Attribute \'%1\' does not exist for entity type \'%2\'.',
                $column->getName(),
                $column->getEntityName()
            ));
        }
    }
}
