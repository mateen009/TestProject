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

class FooterLinks extends \Magezon\SimpleBuilder\Data\Element\AbstractElement
{
    /**
     * Prepare modal components
     */
    public function prepareForm()
    {
        parent::prepareForm();
        $this->prepareFootersTab();
        return $this;
    }

    /**
     * @return \Magezon\Builder\Data\Form\Element\Fieldset
     */
    public function prepareGeneralTab()
    {
        $general = parent::prepareGeneralTab();

        $links = $general->addChildren(
            'footer_links',
            'dynamicRows',
            [
                'key'       => 'footer_links',
                'className' => 'mgz-footer-links-email mgz-editor-simple',
                'sortOrder' => 10
            ]
        );

        $container1 = $links->addContainerGroup(
            'container1',
            [
                'templateOptions' => [
                    'sortOrder' => 20
                ]
            ]
        );

            $container1->addChildren(
                'main_title',
                'text',
                [
                    'sortOrder'       => 10,
                    'key'             => 'main_title',
                    'defaultValue'    => 'TITLE',
                    'templateOptions' => [
                        'label' => __('Main title')
                    ]
                ]
            );

            $link = $container1->addChildren(
                'footer_link',
                'dynamicRows',
                [
                    'key'       => 'footer_link',
                    'className' => 'mgz-footer-link-email mgz-editor-simple',
                    'sortOrder' => 20
                ]
            );

                $link->addChildren(
                    'child_link',
                    'link',
                    [
                    'sortOrder'       => 10,
                    'key'             => 'child_link',
                    'templateOptions' => [
                        'label' => __('Link detail')
                    ]
                    ]
                );

            $link_container = $link->addContainer(
                'container12',
                [
                    'className' => 'mgz-dynamicrows-actions',
                    'sortOrder' => 20
                ]
            );

            $link_container->addChildren(
                'delete',
                'actionDelete',
                [
                        'sortOrder' => 10
                    ]
            );

            $link_container->addChildren(
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

    /**
     * @return \Magezon\Builder\Data\Form\Element\Fieldset
     */
    public function prepareFootersTab()
    {
        $settings = $this->addTab(
            'tab_footer_settings',
            [
                'sortOrder'       => 20,
                'templateOptions' => [
                    'label' => __('Footer Settings')
                ]
            ]
        );

        $container1 = $settings->addContainerGroup(
            'container1',
            [
                'templateOptions' => [
                    'sortOrder' => 10
                ]
            ]
        );

            $container1->addChildren(
                'heading_main_title',
                'select',
                [
                    'sortOrder'       => 10,
                    'key'             => 'heading_main_title',
                    'defaultValue'    => 'h3',
                    'templateOptions' => [
                        'label'   => __('Heading Type'),
                        'options' => $this->getHeadingType()
                    ]
                ]
            );

            $container1->addChildren(
                'title_font_size',
                'number',
                [
                    'sortOrder'       => 20,
                    'key'             => 'title_font_size',
                    'templateOptions' => [
                        'label' => __('Font Size')
                    ]
                ]
            );

            $container1->addChildren(
                'title_color',
                'color',
                [
                    'sortOrder'       => 30,
                    'key'             => 'title_color',
                    'templateOptions' => [
                        'label' => __('Title Color')
                    ]
                ]
            );

            $container1->addChildren(
                'title_uppercase',
                'toggle',
                [
                    'sortOrder'       => 40,
                    'key'             => 'title_uppercase',
                    'templateOptions' => [
                        'label' => __('Uppercase')
                    ]
                ]
            );

        $container2 = $settings->addContainerGroup(
            'container2',
            [
                'templateOptions' => [
                    'sortOrder' => 20
                ]
            ]
        );

            $container2->addChildren(
                'a_font_size',
                'number',
                [
                    'sortOrder'       => 10,
                    'key'             => 'a_font_size',
                    'templateOptions' => [
                        'label' => __('Link Font Size')
                    ]
                ]
            );

            $container2->addChildren(
                'a_color',
                'color',
                [
                    'sortOrder'       => 20,
                    'key'             => 'a_color',
                    'templateOptions' => [
                        'label' => __('Link Color')
                    ]
                ]
            );

        $container3 = $settings->addContainerGroup(
            'container3',
            [
                'templateOptions' => [
                    'sortOrder' => 30
                ]
            ]
        );

            $container3->addChildren(
                'a_hover_font_size',
                'number',
                [
                    'sortOrder'       => 10,
                    'key'             => 'a_hover_font_size',
                    'templateOptions' => [
                        'label' => __('Link Hover Font Size')
                    ]
                ]
            );

            $container3->addChildren(
                'a_hover_color',
                'color',
                [
                    'sortOrder'       => 20,
                    'key'             => 'a_hover_color',
                    'templateOptions' => [
                        'label' => __('Link Hover Color')
                    ]
                ]
            );

        return $settings;
    }
}
