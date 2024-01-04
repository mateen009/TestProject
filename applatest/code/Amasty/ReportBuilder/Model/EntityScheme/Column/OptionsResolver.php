<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme\Column;

use Magento\Eav\Model\Config;
use Amasty\ReportBuilder\Api\ColumnInterface;
use Magento\Framework\Exception\LocalizedException;

class OptionsResolver
{
    /**
     * @var Config
     */
    private $eavConfig;

    public function __construct(
        Config $eavConfig
    ) {
        $this->eavConfig = $eavConfig;
    }

    public function resolve(ColumnInterface $column, bool $asUiOptions = false): array
    {
        $options = [];
        if ($column->getSourceModel()) {
            $source = $column->getSource();
            if (method_exists($source, 'getAttribute')) {
                try {
                    $attribute = $this->eavConfig->getAttribute($column->getEntityName(), $column->getName());
                } catch (LocalizedException $e) {
                    return $options;
                }
                $source->setAttribute($attribute);
            }
            $options = $source->toOptionArray();
        } elseif ($column->getOptions()) {
            $optionsArray = $column->getOptions();
            foreach ($optionsArray as $value => $label) {
                $options[] = ['value' => $value, 'label' => $label];
            }
        }

        if ($asUiOptions) {
            if (!empty($options)) {
                foreach ($options as &$optionData) {
                    $optionData['__disableTmpl'] = true;
                }
            }
        }

        return $options;
    }
}
