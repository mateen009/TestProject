<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Ui\Component\Listing\View\Columns;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Column\AggregationType;
use Amasty\ReportBuilder\Model\EntityScheme\Column\DataType;
use Amasty\ReportBuilder\Model\SelectResolver\Context;
use Amasty\ReportBuilder\Model\SelectResolver\EntitySimpleRelationResolver;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Column\OptionsResolver;

class ColumnFactory
{
    /**
     * @var UiComponentFactory
     */
    private $componentFactory;

    /**
     * @var array
     */
    private $jsComponentMap = [
        'text' => 'Magento_Ui/js/grid/columns/column',
        'select' => 'Magento_Ui/js/grid/columns/select',
        'multiselect' => 'Magento_Ui/js/grid/columns/select',
        'date' => 'Magento_Ui/js/grid/columns/date',
    ];

    /**
     * @var array
     */
    private $dataTypeMap = [
        'default' => 'text',
        'text' => 'text',
        'boolean' => 'select',
        'select' => 'select',
        'multiselect' => 'multiselect',
        'date' => 'date',
        'datetime' => 'date',
        'timestamp' => 'date',
    ];

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var OptionsResolver
     */
    private $optionsResolver;

    /**
     * @var \Amasty\ReportBuilder\Model\ReportResolver
     */
    private $reportResolver;

    /**
     * @var \Amasty\ReportBuilder\Model\EntityScheme\Provider
     */
    private $schemeProvider;

    /**
     * @var EntitySimpleRelationResolver
     */
    private $simpleRelationResolver;

    public function __construct(
        Context $context,
        UiComponentFactory $componentFactory,
        TimezoneInterface $timezone,
        OptionsResolver $optionsResolver,
        EntitySimpleRelationResolver $simpleRelationResolver
    ) {
        $this->componentFactory = $componentFactory;
        $this->timezone = $timezone;
        $this->optionsResolver = $optionsResolver;
        $this->reportResolver = $context->getReportResolver();
        $this->schemeProvider = $context->getEntitySchemeProvider();
        $this->simpleRelationResolver = $simpleRelationResolver;
    }

    /**
     * @param array $columnData
     * @param ColumnInterface $column
     * @param ContextInterface $context
     * @param array $config
     * @return UiComponentInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function create(
        array $columnData,
        ColumnInterface $column,
        ContextInterface $context,
        array $config = []
    ): UiComponentInterface {
        $parentColumn = $column->getParentColumn() ?? $column;
        $label = $columnData[ColumnInterface::CUSTOM_TITLE] ?: $column->getTitle() ?: $parentColumn->getTitle();
        $report = $this->reportResolver->resolve();

        $config = array_merge(
            [
                'label' => $label,
                'dataType' => $this->getDataType($parentColumn->getType()),
                'add_field' => true,
                'visible' => true,
            ],
            $config
        );

        if ($config['dataType'] === 'date') {
            $scheme = $this->schemeProvider->getEntityScheme();
            $entity = $scheme->getEntityByName($report->getMainEntity());
            if ($report->getUsePeriod()
                && $entity->getPeriodColumn()->getColumnId() == $columnData['column_id']
            ) {
                $config += $this->getDateConfig('date');
            } else {
                $config += $this->getDateConfig($parentColumn->getType());
            }

        }

        if (!$columnData[ColumnInterface::IS_DATE_FILTER]) {
            $config['filter'] = $parentColumn->getFrontendModel();
        }

        $config['options'] = $this->optionsResolver->resolve($column, true);
        $config['aggregation_type'] = $columnData[ColumnInterface::AGGREGATION_TYPE]
            ?: $parentColumn->getAggregationType();
        $config['entity_name'] = $column->getEntityName();
        $config['component'] = $this->jsComponentMap[$config['dataType']];
        if (!empty($config['options'])) {
            $config['component'] = $this->jsComponentMap['select'];
        }

        $arguments = [
            'data' => [
                'config' => $config,
            ],
            'context' => $context
        ];

        if ($parentColumn->getData('ui_grid_class')) {
            $arguments['config']['class'] = $parentColumn->getData('ui_grid_class');
        }

        $component = $this->componentFactory->create(
            str_replace('.', '_', $columnData['column_id']),
            'column',
            $arguments
        );

        return $component;
    }

    private function getDataType(string $type): string
    {
        return $this->dataTypeMap[$type] ?? $this->dataTypeMap['default'];
    }

    private function getDateConfig(string $type): array
    {
        if (in_array($type, [DataType::DATETIME, DataType::TIMESTAMP])) {
            $dateFormat = $this->timezone->getDateTimeFormat(\IntlDateFormatter::MEDIUM);
            $timezone = $this->timezone->getConfigTimezone();
        } else {
            $dateFormat = $this->timezone->getDateFormat(\IntlDateFormatter::MEDIUM);
            $timezone = $this->timezone->getDefaultTimezone();
        }

        return [
            'timezone' => $timezone,
            'dateFormat' => $dateFormat,
            'options' => ['showsTime' => $type === 'datetime'],
        ];
    }
}
