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

class Menu extends \Magezon\SimpleBuilder\Data\Element\AbstractElement
{
    /**
     * Prepare modal components
     */
    public function prepareForm()
    {
        parent::prepareForm();
        $this->prepareMenusTab();
        return $this;
    }

    /**
     * @return \Magezon\Builder\Data\Form\Element\Fieldset
     */
    public function prepareGeneralTab()
    {
        $general = parent::prepareGeneralTab();

        $menus = $general->addChildren(
            'menu_items',
            'dynamicRows',
            [
                'key'       => 'menu_items',
                'className' => 'mgz-email-menu-items mgz-editor-simple',
                'sortOrder' => 10
            ]
        );

            $container1 = $menus->addContainerGroup(
                'container1',
                [
                    'templateOptions' => [
                        'sortOrder' => 10
                    ]
                ]
            );

                $container11 = $container1->addContainer(
                    'container11',
                    [
                        'className'       => 'mgz-width80',
                        'templateOptions' => [
                            'sortOrder' => 10
                        ]
                    ]
                );
                    $container11->addChildren(
                        'menu_link',
                        'link',
                        [
                            'sortOrder'       => 20,
                            'key'             => 'menu_link',
                            'templateOptions' => [
                                'label' => __('Menu URL')
                            ]
                        ]
                    );

                $container12 = $container1->addContainer(
                    'container12',
                    [
                        'className' => 'mgz-dynamicrows-actions',
                        'sortOrder' => 20
                    ]
                );

                    $container12->addChildren(
                        'delete',
                        'actionDelete',
                        [
                            'sortOrder' => 10
                        ]
                    );

                    $container12->addChildren(
                        'position',
                        'text',
                        [
                            'sortOrder'       => 20,
                            'key'             => 'position',
                            'templateOptions' => [
                                'element' => 'Magezon_Builder/js/form/element/dynamic-rows/position'
                            ]
                        ]
                    );
        
        return $general;
    }

    public function prepareMenusTab()
    {
        $settings = $this->addTab(
            'tab_social_settings',
            [
                'sortOrder'       => 20,
                'templateOptions' => [
                    'label' => __('Menus Settings')
                ]
            ]
        );

        $container1 = $settings->addContainerGroup(
            'container1',
            [
                'sortOrder' => 10,
                'templateOptions' => [
                    'label' => __('General'),
                ]
            ]
        );

            $container1->addChildren(
                'background_color',
                'color',
                [
                    'sortOrder'       => 10,
                    'key'             => 'background_color',
                    'templateOptions' => [
                        'label'       => __('Background Color')
                    ]
                ]
            );

            $container1->addChildren(
                'a_color',
                'color',
                [
                    'sortOrder'       => 20,
                    'key'             => 'a_color',
                    'templateOptions' => [
                        'label'       => __('Color')
                    ]
                ]
            );

            $container1->addChildren(
                'a_font_size',
                'number',
                [
                    'sortOrder'       => 30,
                    'key'             => 'a_font_size',
                    'templateOptions' => [
                        'label' => __('Font Size')
                    ]
                ]
            );

            $container1->addChildren(
                'a_border_radius',
                'number',
                [
                    'sortOrder'       => 30,
                    'key'             => 'a_border_radius',
                    'templateOptions' => [
                        'label' => __('Border Radius')
                    ]
                ]
            );

        $container2 = $settings->addContainerGroup(
            'container2',
            [
                'sortOrder' => 20,
                'templateOptions' => [
                    'label' => __('Hover Settings'),
                ]
            ]
        );

            $container2->addChildren(
                'a_background_color_hover',
                'color',
                [
                    'sortOrder'       => 10,
                    'key'             => 'a_background_color_hover',
                    'templateOptions' => [
                        'label'       => __('Background Color')
                    ]
                ]
            );

            $container2->addChildren(
                'color_a_hover',
                'color',
                [
                    'sortOrder'       => 20,
                    'key'             => 'a_color_hover',
                    'templateOptions' => [
                        'label'       => __('Color')
                    ]
                ]
            );

            $container2->addChildren(
                'a_font_size_hover',
                'number',
                [
                    'sortOrder'       => 30,
                    'key'             => 'a_font_size_hover',
                    'templateOptions' => [
                        'label' => __('Font Size')
                    ]
                ]
            );

        return $settings;
    }
}
