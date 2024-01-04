<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Source;

use Amasty\ReportBuilder\Model\EntityScheme\Provider;

class Entity implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var Provider
     */
    private $entitySchemeProvider;

    public function __construct(Provider $provider)
    {
        $this->entitySchemeProvider = $provider;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $options =  [['value' => '', 'label' => __('--Please Select Entity--')]];
        $scheme = $this->entitySchemeProvider->getEntityScheme();

        foreach ($scheme->getAllEntitiesOptionArray(true) as $name => $title) {
            $options[] = [
                'value' => $name,
                'label' => $title
            ];
        }

        return $options;
    }
}
