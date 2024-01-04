<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://magezon.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_EmailBuilder
 * @copyright Copyright (C) 2020 Magezon (https://magezon.com)
 */

namespace Magezon\EmailBuilder\Data\Element;

class LoadTemplate extends \Magezon\SimpleBuilder\Data\Element\AbstractElement
{
    /**
     * @var \Magento\Email\Model\Template\Config
     */
    private $_emailConfig;

    /**
     * @var \Magento\Variable\Model\Source\Variables
     */
    protected $_variables;

    /**
     * @var \Magento\Variable\Model\VariableFactory
     */
    protected $_variableFactory;

    /**
     * LoadTemplate constructor.
     * @param \Magezon\Builder\Data\FormFactory $formFactory
     * @param \Magezon\Builder\Helper\Data $builderHelper
     * @param \Magento\Email\Model\Template\Config $emailConfig
     * @param \Magento\Variable\Model\VariableFactory $variableFactory
     * @param \Magento\Variable\Model\Source\Variables $variables
     * @param array $data
     */
    public function __construct(
        \Magezon\Builder\Data\FormFactory $formFactory,
        \Magezon\Builder\Helper\Data $builderHelper,
        \Magento\Email\Model\Template\Config $emailConfig,
        \Magento\Variable\Model\VariableFactory $variableFactory,
        \Magento\Variable\Model\Source\Variables $variables,
        array $data = []
    ) {
        parent::__construct($formFactory, $builderHelper, $data);
        $this->_emailConfig = $emailConfig;
        $this->_variableFactory = $variableFactory;
        $this->_variables = $variables;
    }

    /**
     * Prepare modal components
     */
    public function prepareForm()
    {
        parent::prepareForm();
        $this->prepareLoadTemplateTab();
        return $this;
    }

    /**
     * @return \Magezon\Builder\Data\Form\Element\Fieldset
     */
    public function prepareGeneralTab()
    {
        $general = parent::prepareGeneralTab();

        $container1 = $general->addContainerGroup(
            'container1',
            [
                'sortOrder' => 10
            ]
        );

            $container1->addChildren(
                'template_id',
                'select',
                [
                    'sortOrder'       => 10,
                    'key'             => 'template_id',
                    'defaultValue'    => '',
                    'templateOptions' => [
                        'element' => 'Magezon_EmailBuilder/js/form/element/template',
                        'label'   => __('Templates'),
                        'options' => $this->getConfigTemplates()
                    ]
                ]
            );

            $container1->addChildren(
                'variable',
                'select',
                [
                    'sortOrder'       => 10,
                    'key'             => 'variable',
                    'defaultValue'    => '',
                    'templateOptions' => [
                        'element' => 'Magezon_EmailBuilder/js/form/element/variable',
                        'label'   => __('Variables'),
                        'options' => $this->getVariables()
                    ]
                ]
            );

        $container2 = $general->addContainerGroup(
            'container2',
            [
                'sortOrder' => 20
            ]
        );

            $container2->addChildren(
                'email_content',
                'textarea',
                [
                    'sortOrder'       => 10,
                    'key'             => 'email_content',
                    'defaultValue'    => '',
                    'templateOptions' => [
                        'label' => __('Email Content'),
                        'rows'  => 16,
                        'note'  => __('Enter your email content.')
                    ]
                ]
            );

        return $general;
    }

    /**
     * @return \Magezon\Builder\Data\Form\Element\Fieldset
     */
    public function prepareLoadTemplateTab()
    {
        $settings = $this->addTab(
            'tab_load_tpl_settings',
            [
                'sortOrder'       => 20,
                'templateOptions' => [
                    'label' => __('Content Settings')
                ]
            ]
        );

        $container1 = $settings->addContainerGroup(
            'container1',
            [
                'sortOrder' => 10
            ]
        );

            $container1->addChildren(
                'font_size',
                'number',
                [
                    'sortOrder'       => 10,
                    'key'             => 'font_size',
                    'templateOptions' => [
                        'label' => __('Font Size')
                    ]
                ]
            );

            $container1->addChildren(
                'color',
                'color',
                [
                    'key'             => 'color',
                    'sortOrder'       => 20,
                    'templateOptions' => [
                        'label' => __('Text Color')
                    ]
                ]
            );

        $container2 = $settings->addContainerGroup(
            'container2',
            [
                'sortOrder' => 20
            ]
        );

            $container2->addChildren(
                'line_height',
                'text',
                [
                    'sortOrder'       => 10,
                    'key'             => 'line_height',
                    'templateOptions' => [
                        'label' => __('Line Height')
                    ]
                ]
            );

            $container2->addChildren(
                'font_weight',
                'text',
                [
                    'sortOrder'       => 20,
                    'key'             => 'font_weight',
                    'templateOptions' => [
                        'label' => __('Font Weight')
                    ]
                ]
            );
    }

    /**
     * Return list of all email templates, both default module and theme-specific templates
     *
     * @return array[]
     */
    protected function getConfigTemplates()
    {
        return $this->_emailConfig->getAvailableTemplates();
    }

    /**
     * Retrieve variables to insert into email
     *
     * @return array
     */
    protected function getVariables()
    {
        $variables = $this->_variables->toOptionArray(true);
        $customVariables = $this->_variableFactory->create()->getVariablesOptionArray(true);
        if ($customVariables) {
            $variables = array_merge_recursive($variables, $customVariables);
        }

        $vars = [];
        foreach ($variables as $variable) {
            foreach ($variable['value'] as $item) {
                $vars[] = [
                    'value' => $item['value'],
                    'label' => $item['label']->getText(),
                    'group' => $variable['label']
                ];
            }
        }

        return $vars;
    }
}
