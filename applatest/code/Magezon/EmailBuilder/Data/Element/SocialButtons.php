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

class SocialButtons extends \Magezon\SimpleBuilder\Data\Element\AbstractElement
{
    /**
     * Prepare modal components
     */
    public function prepareForm()
    {
        parent::prepareForm();
        $this->prepareSocialsTab();
        return $this;
    }

    /**
     * @return \Magezon\Builder\Data\Form\Element\Fieldset
     */
    public function prepareGeneralTab()
    {
        $general = parent::prepareGeneralTab();

        $social = $general->addChildren(
            'social_items',
            'dynamicRows',
            [
                'key' => 'social_items',
                'className' => 'mgz-email-social-items mgz-editor-simple',
                'sortOrder' => 10,
                'note'  => __('You should add the social numbers such as 2,3,4,6')
            ]
        );

        $container1 = $general->addContainerGroup(
            'container1',
            [
                'templateOptions' => [
                    'sortOrder' => 10
                ]
            ]
        );

            $container1->addChildren(
                'text_follow',
                'text',
                [
                    'sortOrder'       => 10,
                    'key'             => 'text_follow',
                    'defaultValue'    => 'Follow us',
                    'templateOptions' => [
                        'label' => __('Main Title')
                    ]
                ]
            );

        $container2 = $social->addContainerGroup(
            'container2',
            [
                'templateOptions' => [
                    'sortOrder' => 20
                ]
            ]
        );

            $container21 = $container2->addContainer(
                'container21',
                [
                    'className'       => 'mgz-width80',
                    'templateOptions' => [
                        'sortOrder' => 10
                    ]
                ]
            );

            $container21->addChildren(
                'social_img',
                'image',
                [
                    'sortOrder'       => 10,
                    'key'             => 'social_img',
                    'defaultValue'    => '',
                    'templateOptions' => [
                        'label' => __('Logo'),
                        'note'  => __('You can find the icons in emailbuilder folder')
                    ]
                ]
            );

            $container21->addChildren(
                'social_url',
                'link',
                [
                    'sortOrder'       => 20,
                    'key'             => 'social_url',
                    'templateOptions' => [
                        'label' => __('URL')
                    ]
                ]
            );

        $container3 = $container2->addContainer(
            'container3',
            [
                'className' => 'mgz-dynamicrows-actions',
                'sortOrder' => 30
            ]
        );

            $container3->addChildren(
                'social_delete',
                'actionDelete',
                [
                    'sortOrder' => 10
                ]
            );

            $container3->addChildren(
                'social_position',
                'text',
                [
                    'sortOrder'       => 20,
                    'key'             => 'position',
                    'templateOptions' => [
                        'element' => 'Magezon_Builder/js/form/element/dynamic-rows/position'
                    ]
                ]
            );
    }

    /**
     * @return \Magezon\Builder\Data\Form\Element\Fieldset
     */
    public function prepareSocialsTab()
    {
        $settings = $this->addTab(
            'tab_social_settings',
            [
                'sortOrder'       => 20,
                'templateOptions' => [
                    'label' => __('Socials Settings')
                ]
            ]
        );

        $container1 = $settings->addContainerGroup(
            'container1',
            [
                'templateOptions' => [
                    'sortOrder' => 10,
                    'label' => __('Text Settings'),
                ]
            ]
        );
            $container1->addChildren(
                'text_display',
                'toggle',
                [
                    'sortOrder'       => 5,
                    'key'             => 'text_display',
                    'defaultValue'    => true,
                    'templateOptions' => [
                        'label' => __('Display Text')
                    ]
                ]
            );

            $container1->addChildren(
                'text_font_size',
                'number',
                [
                    'sortOrder'       => 10,
                    'key'             => 'text_font_size',
                    'defaultValue'    => 20,
                    'templateOptions' => [
                        'label' => __('Font Size')
                    ]
                ]
            );

            $container1->addChildren(
                'text_font_weight',
                'select',
                [
                    'sortOrder'       => 20,
                    'key'             => 'text_font_weight',
                    'defaultValue'    => 'bold',
                    'templateOptions' => [
                        'label'   => __('Font Weight'),
                        'options' => $this->getFontWeightValues()
                    ]
                ]
            );

            $container1->addChildren(
                'text_color',
                'color',
                [
                    'sortOrder'       => 30,
                    'key'             => 'text_color',
                    'templateOptions' => [
                        'label' => __('Title Color')
                    ]
                ]
            );

            $container1->addChildren(
                'text_padding',
                'number',
                [
                    'sortOrder'       => 40,
                    'key'             => 'text_padding',
                    'defaultValue'    => 20,
                    'templateOptions' => [
                        'label' => __('Padding Top')
                    ]
                ]
            );

        $container2 = $settings->addContainerGroup(
            'container2',
            [
                'templateOptions' => [
                    'sortOrder' => 20,
                    'label' => __('Image Settings'),
                ]
            ]
        );

            $container2->addChildren(
                'img_width',
                'number',
                [
                    'sortOrder'       => 10,
                    'key'             => 'img_width',
                    'defaultValue'    => 50,
                    'templateOptions' => [
                        'label' => __('Width')
                    ]
                ]
            );

            $container2->addChildren(
                'img_height',
                'number',
                [
                    'sortOrder'       => 20,
                    'key'             => 'img_height',
                    'defaultValue'    => 50,
                    'templateOptions' => [
                        'label' => __('Height')
                    ]
                ]
            );

            $container2->addChildren(
                'img_border',
                'toggle',
                [
                    'sortOrder'       => 30,
                    'key'             => 'img_border',
                    'defaultValue'    => true,
                    'templateOptions' => [
                        'label' => __('Border Image')
                    ]
                ]
            );

        $container3 = $settings->addContainerGroup(
            'container3',
            [
                'templateOptions' => [
                    'sortOrder' => 30,
                    'label' => __('Image Hover Settings'),
                ]
            ]
        );

            $container3->addChildren(
                'img_opacity',
                'select',
                [
                    'sortOrder'       => 10,
                    'key'             => 'img_opacity',
                    'defaultValue'    => '0.5',
                    'templateOptions' => [
                        'label'   => __('Opacity'),
                        'options' => $this->getOpacityValues()
                    ]
                ]
            );

        $container4 = $settings->addContainerGroup(
            'container4',
            [
                'templateOptions' => [
                    'sortOrder' => 40
                ]
            ]
        );

            $container4->addChildren(
                'display_type',
                'select',
                [
                    'sortOrder'       => 10,
                    'key'             => 'display_type',
                    'defaultValue'    => 'bootstrap',
                    'templateOptions' => [
                        'label'   => __('Display Types'),
                        'options' => $this->getDisplayTypes()
                    ]
                ]
            );

        return $settings;
    }

    /**
     * @return array
     */
    private function getFontWeightValues()
    {
        return [
            [
                'label' => 'normal',
                'value' => 'normal'
            ],
            [
                'label' => 'bold',
                'value' => 'bold'
            ],
            [
                'label' => 'border',
                'value' => 'border'
            ],
            [
                'label' => 'lighter',
                'value' => 'lighter'
            ],
            [
                'label' => '100',
                'value' => '100'
            ],
            [
                'label' => '200',
                'value' => '200'
            ],
            [
                'label' => '300',
                'value' => '300'
            ],
            [
                'label' => '400',
                'value' => '400'
            ],
            [
                'label' => '500',
                'value' => '500'
            ],
            [
                'label' => '600',
                'value' => '600'
            ],
            [
                'label' => '700',
                'value' => '700'
            ],
            [
                'label' => '800',
                'value' => '800'
            ],
            [
                'label' => '900',
                'value' => '900'
            ],
            [
                'label' => 'initial',
                'value' => 'initial'
            ],
            [
                'label' => 'inherit',
                'value' => 'inherit'
            ]
        ];
    }

    /**
     * @return array
     */
    private function getOpacityValues()
    {
        return [
            [
                'label' => '0.1',
                'value' => '0.1'
            ],
            [
                'label' => '0.2',
                'value' => '0.2'
            ],
            [
                'label' => '0.3',
                'value' => '0.3'
            ],
            [
                'label' => '0.4',
                'value' => '0.4'
            ],
            [
                'label' => '0.5',
                'value' => '0.5'
            ],
            [
                'label' => '0.6',
                'value' => '0.6'
            ],
            [
                'label' => '0.7',
                'value' => '0.7'
            ],
        ];
    }

    /**
     * @return array
     */
    private function getDisplayTypes()
    {
        return [
            [
                'label' => __('List'),
                'value' => 'list'
            ],
            [
                'label' => __('Bootstrap Column'),
                'value' => 'bootstrap'
            ]
        ];
    }
}
