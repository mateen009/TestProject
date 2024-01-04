<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_SimpleBuilder
 * @copyright Copyright (C) 2020 Magezon (https://www.magezon.com)
 */

namespace Magezon\SimpleBuilder\Data\Element;

abstract class AbstractElement extends \Magezon\Builder\Data\Element\AbstractElement
{
    /**
     * @return Magezon\Builder\Data\Form\Element\Fieldset
     */
    public function getFormTab()
    {
        if ($this->_formTab == NULL) {
            $this->_formTab = $this->getForm()->addTab('tab', [
                'className'       => 'mgz-modal-tab',
                'templateOptions' => [
                    'singleMode' => false
                ]
            ]);
        }
        return $this->_formTab;
    }

    /**
     * Prepare modal components
     */
    public function prepareForm()
    {
        $this->prepareGeneralTab();
        $this->prepareDesignTab();
        return $this;
    }

    /**
     * @return Magezon\Builder\Data\Form\Element\Fieldset
     */
    public function prepareGeneralTab()
    {
        $general = $this->addTab(
            self::TAB_GENERAL,
            [
                'sortOrder'       => 0,
                'templateOptions' => [
                    'label' => __('General')
                ]
            ]
        );

        return $general;
    }

    /**
     * @return Magezon\Builder\Data\Form\Element\Fieldset
     */
    public function prepareDesignTab()
    {
        $design = $this->addTab(
            self::TAB_DESIGN,
            [
                'sortOrder'       => 100,
                'templateOptions' => [
                    'label' => __('Design Options')
                ]
            ]
        );

        	$this->prepareModeCssBox($design, '');
    }

    public function prepareModeCssBox($parent, $prefix = '')
    {
        $parent->addChildren(
            'simply',
            'checkbox',
            [
                'key'             => $prefix . 'simply',
                'className'       => 'mgz-design-simply',
                'sortOrder'       => 0,
                'templateOptions' => [
                    'element'       => 'Magezon_Builder/js/form/element/simply',
                    'checkboxLabel' => __('Simplify Controls'),
                    'prefix'        => $prefix
                ]
            ]
        );

            $borderRadius = $parent->addFieldset(
                'radius',
                [
                    'sortOrder'       => 10,
                    'className'       => 'mgz-design-layout',
                    'templateOptions' => [
                        'label' => __('Radius'),
                        'focus' => true
                    ]
                ]
            );

                $borderRadius->addChildren(
                    'border_top_left_radius',
                    'text',
                    [
                        'key'             => $prefix . 'border_top_left_radius',
                        'className'       => 'mgz-design-top',
                        'sortOrder'       => 10,
                        'templateOptions' => [
                            'label'       => __('Border Radius Top Left'),
                            'placeholder' => '-'
                        ],
                        'expressionProperties' => [
                            'templateOptions.disabled' => 'model.' . $prefix . 'simply'
                        ]
                    ]
                );

                $borderRadius->addChildren(
                    'border_top_right_radius',
                    'text',
                    [
                        'key'             => $prefix . 'border_top_right_radius',
                        'className'       => 'mgz-design-right',
                        'sortOrder'       => 20,
                        'templateOptions' => [
                            'label'       => __('Border Top Radius Right'),
                            'placeholder' => '-'
                        ]
                    ]
                );

                $borderRadius->addChildren(
                    'border_bottom_right_radius',
                    'text',
                    [
                        'key'             => $prefix . 'border_bottom_right_radius',
                        'className'       => 'mgz-design-bottom',
                        'sortOrder'       => 30,
                        'templateOptions' => [
                            'label'       => __('Border Radius Bottom Right'),
                            'placeholder' => '-'
                        ],
                        'expressionProperties' => [
                            'templateOptions.disabled' => 'model.' . $prefix . 'simply'
                        ]
                    ]
                );

                $borderRadius->addChildren(
                    'border_bottom_left_radius',
                    'text',
                    [
                        'key'             => $prefix . 'border_bottom_left_radius',
                        'className'       => 'mgz-design-left',
                        'sortOrder'       => 40,
                        'templateOptions' => [
                            'label'       => __('Border Radius Bottom Left'),
                            'placeholder' => '-'
                        ],
                        'expressionProperties' => [
                            'templateOptions.disabled' => 'model.' . $prefix . 'simply'
                        ]
                    ]
                );

                    $margin = $borderRadius->addFieldset(
                        'margin',
                        [
                            'sortOrder'       => 100,
                            'templateOptions' => [
                                'label' => __('Margin')
                            ]
                        ]
                    );

                        $margin->addChildren(
                            'margin_top',
                            'text',
                            [
                                'key'             => $prefix . 'margin_top',
                                'className'       => 'mgz-design-top',
                                'sortOrder'       => 10,
                                'templateOptions' => [
                                    'label'       => __('Margin Top'),
                                    'placeholder' => '-'
                                ]
                            ]
                        );

                        $margin->addChildren(
                            'margin_right',
                            'text',
                            [
                                'key'             => $prefix . 'margin_right',
                                'className'       => 'mgz-design-right',
                                'sortOrder'       => 20,
                                'templateOptions' => [
                                    'label'       => __('Margin Right'),
                                    'placeholder' => '-'
                                ],
                                'expressionProperties' => [
                                    'templateOptions.disabled' => 'model.' . $prefix . 'simply'
                                ]
                            ]
                        );

                        $margin->addChildren(
                            'margin_bottom',
                            'text',
                            [
                                'key'             => $prefix . 'margin_bottom',
                                'className'       => 'mgz-design-bottom',
                                'sortOrder'       => 30,
                                'templateOptions' => [
                                    'label' => __('Margin Bottom'),
                                    'placeholder' => '-'
                                ],
                                'expressionProperties' => [
                                    'templateOptions.disabled' => 'model.' . $prefix . 'simply'
                                ]
                            ]
                        );

                        $margin->addChildren(
                            'margin_left',
                            'text',
                            [
                                'key'             => $prefix . 'margin_left',
                                'className'       => 'mgz-design-left',
                                'sortOrder'       => 40,
                                'templateOptions' => [
                                    'label'       => __('Margin Left'),
                                    'placeholder' => '-'
                                ],
                                'expressionProperties' => [
                                    'templateOptions.disabled' => 'model.' . $prefix . 'simply'
                                ]
                            ]
                        );

                        $border = $margin->addFieldset(
                            'border',
                            [
                                'sortOrder'       => 50,
                                'className'       => 'mgz-design-border',
                                'templateOptions' => [
                                    'label' => __('Border')
                                ]
                            ]
                        );

                            $border->addChildren(
                                'border_top_width',
                                'text',
                                [
                                    'key'             => $prefix . 'border_top_width',
                                    'className'       => 'mgz-design-top',
                                    'sortOrder'       => 10,
                                    'templateOptions' => [
                                        'label'       => __('Border Top Width'),
                                        'placeholder' => '-'
                                    ]
                                ]
                            );

                            $border->addChildren(
                                'border_right_width',
                                'text',
                                [
                                    'key'             => $prefix . 'border_right_width',
                                    'className'       => 'mgz-design-right',
                                    'sortOrder'       => 20,
                                    'templateOptions' => [
                                        'label'       => __('Border Right Width'),
                                        'placeholder' => '-'
                                    ],
                                    'expressionProperties' => [
                                        'templateOptions.disabled' => 'model.' . $prefix . 'simply'
                                    ]
                                ]
                            );

                            $border->addChildren(
                                'border_bottom_width',
                                'text',
                                [
                                    'key'             => $prefix . 'border_bottom_width',
                                    'className'       => 'mgz-design-bottom',
                                    'sortOrder'       => 30,
                                    'templateOptions' => [
                                        'label'       => __('Border Bottom Width'),
                                        'placeholder' => '-'
                                    ],
                                    'expressionProperties' => [
                                        'templateOptions.disabled' => 'model.' . $prefix . 'simply'
                                    ]
                                ]
                            );

                            $border->addChildren(
                                'border_left_width',
                                'text',
                                [
                                    'key'             => $prefix . 'border_left_width',
                                    'className'       => 'mgz-design-left',
                                    'sortOrder'       => 40,
                                    'templateOptions' => [
                                        'label'       => __('Border Left Width'),
                                        'placeholder' => '-'
                                    ],
                                    'expressionProperties' => [
                                        'templateOptions.disabled' => 'model.' . $prefix . 'simply'
                                    ]
                                ]
                            );

                            $padding = $border->addFieldset(
                                'padding',
                                [
                                    'sortOrder'       => 50,
                                    'templateOptions' => [
                                        'label' => __('Padding')
                                    ]
                                ]
                            );

                                $padding->addChildren(
                                    'padding_top',
                                    'text',
                                    [
                                        'key'             => $prefix . 'padding_top',
                                        'className'       => 'mgz-design-top',
                                        'sortOrder'       => 10,
                                        'templateOptions' => [
                                            'label'       => __('Padding Top'),
                                            'placeholder' => '-'
                                        ]
                                    ]
                                );

                                $padding->addChildren(
                                    'padding_right',
                                    'text',
                                    [
                                        'key'             => $prefix . 'padding_right',
                                        'className'       => 'mgz-design-right',
                                        'sortOrder'       => 20,
                                        'templateOptions' => [
                                            'label'       => __('Padding Right'),
                                            'placeholder' => '-'
                                        ],
                                        'expressionProperties' => [
                                            'templateOptions.disabled' => 'model.' . $prefix . 'simply'
                                        ]
                                    ]
                                );

                                $padding->addChildren(
                                    'padding_bottom',
                                    'text',
                                    [
                                        'key'             => $prefix . 'padding_bottom',
                                        'className'       => 'mgz-design-bottom',
                                        'sortOrder'       => 30,
                                        'templateOptions' => [
                                            'label'       => __('Padidng Bottom'),
                                            'placeholder' => '-'
                                        ],
                                        'expressionProperties' => [
                                            'templateOptions.disabled' => 'model.' . $prefix . 'simply'
                                        ]
                                    ]
                                );

                                $padding->addChildren(
                                    'padding_left',
                                    'text',
                                    [
                                        'key'             => $prefix . 'padding_left',
                                        'className'       => 'mgz-design-left',
                                        'sortOrder'       => 40,
                                        'templateOptions' => [
                                            'label'       => __('Padding Left'),
                                            'placeholder' => '-',
                                            'disabled'    => 'model.' . $prefix . 'simply'
                                        ],
                                        'expressionProperties' => [
                                            'templateOptions.disabled' => 'model.' . $prefix . 'simply'
                                        ]
                                    ]
                                );

                                $padding->addChildren(
                                    'padding_unit',
                                    'html',
                                    [
                                        'sortOrder'       => 50,
                                        'templateOptions' => [
                                            'content' => '<div class="mgz-design-logo"></div>'
                                        ]
                                    ]
                                );

        $container2 = $parent->addContainer(
            'container2',
            [
                'className' => 'mgz-design-styling',
                'sortOrder' => 20
            ]
        );

            $container2->addChildren(
                'border_color',
                'color',
                [
                    'key'             => $prefix . 'border_color',
                    'sortOrder'       => 10,
                    'templateOptions' => [
                        'label' => __('Border Color')
                    ]
                ]
            );

            $container2->addChildren(
                'border_style',
                'select',
                [
                    'key'             => $prefix . 'border_style',
                    'sortOrder'       => 20,
                    'templateOptions' => [
                        'label'       => __('Border Style'),
                        'options'     => $this->getBorderStyle(),
                        'placeholder' => __('Theme defaults')
                    ]
                ]
            );

        $container3 = $parent->addContainerGroup(
            'container3',
            [
                'sortOrder' => 40
            ]
        );

            $container3->addChildren(
                'background_image',
                'image',
                [
                    'key'             => $prefix . 'background_image',
                    'sortOrder'       => 10,
                    'templateOptions' => [
                        'label' => __('Background Image')
                    ]
                ]
            );

            $container3->addChildren(
                'background_color',
                'color',
                [
                    'key'             => $prefix . 'background_color',
                    'sortOrder'       => 20,
                    'templateOptions' => [
                        'label' => __('Background Color')
                    ]
                ]
            );

        $container4 = $parent->addContainerGroup(
            'container4',
            [
                'sortOrder'      => 40,
                'hideExpression' => '!model.' . $prefix . 'background_image'
            ]
        );

            $container4->addChildren(
                'background_style',
                'select',
                [
                    'key'             => $prefix . 'background_style',
                    'sortOrder'       => 10,
                    'defaultValue'    => 'no-repeat',
                    'templateOptions' => [
                        'label'   => __('Background Style'),
                        'options' => $this->getBackgroundStyle()
                    ]
                ]
            );

            $container4->addChildren(
                'background_position',
                'select',
                [
                    'key'             => $prefix . 'background_position',
                    'defaultValue'    => 'center-top',
                    'sortOrder'       => 20,
                    'templateOptions' => [
                        'label'   => __('Background Position'),
                        'options' => $this->getBackgroundPosition()
                    ]
                ]
            );
    }

    /**
     * @return array
     */
    public function getBackgroundType()
    {
        return [
            [
                'label' => __('Image'),
                'value' => 'image'
            ]
        ];
    }
}