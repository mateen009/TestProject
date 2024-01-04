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
 * @package   Magezon_
 * @copyright Copyright (C) 2020 Magezon (https://magezon.com)
 */

namespace Magezon\EmailBuilder\Data\Element;

class Logo extends \Magezon\SimpleBuilder\Data\Element\AbstractElement
{
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
                'source',
                'select',
                [
                    'sortOrder'       => 10,
                    'key'             => 'source',
                    'defaultValue'    => 'media_library',
                    'templateOptions' => [
                        'label'   => __('Logo Source'),
                        'options' => $this->getSource()
                    ]
                ]
            );

        $general->addChildren(
            'custom_src',
            'text',
            [
                'sortOrder'       => 20,
                'key'             => 'custom_src',
                'templateOptions' => [
                    'label' => __('External link')
                ],
                'hideExpression' => 'model.source!="external_link"'
            ]
        );

        $container2 = $general->addContainerGroup(
            'container2',
            [
                'sortOrder'      => 30,
                'hideExpression' => 'model.source!="media_library"'
            ]
        );

            $container2->addChildren(
                'logo_img',
                'image',
                [
                    'sortOrder'       => 10,
                    'key'             => 'logo_img',
                    'defaultValue'    => '',
                    'templateOptions' => [
                        'label' => __('Logo')
                    ]
                ]
            );

        $container3 = $general->addContainerGroup(
            'container4',
            [
                'sortOrder' => 40
            ]
        );

            $container3->addChildren(
                'logo_width',
                'number',
                [
                    'sortOrder'       => 10,
                    'key'             => 'logo_width',
                    'defaultValue'    => '200',
                    'templateOptions' => [
                        'label' => __('Logo Width')
                    ]
                ]
            );

            $container3->addChildren(
                'logo_height',
                'number',
                [
                    'sortOrder'       => 20,
                    'key'             => 'logo_height',
                    'defaultValue'    => '200',
                    'templateOptions' => [
                        'label' => __('Logo Height')
                    ]
                ]
            );

        $container4 = $general->addContainerGroup(
            'container5',
            [
                'sortOrder' => 50
            ]
        );

            $container4->addChildren(
                'alt_tag',
                'text',
                [
                    'sortOrder'       => 10,
                    'key'             => 'alt_tag',
                    'templateOptions' => [
                        'label' => __('Alternative Text')
                    ]
                ]
            );

            $container4->addChildren(
                'logo_id',
                'text',
                [
                    'sortOrder'       => 20,
                    'key'             => 'logo_id',
                    'templateOptions' => [
                        'label' => __('Logo ID')
                    ]
                ]
            );

        return $general;
    }

    /**
     * @return array
     */
    public function getSource()
    {
        return [
            [
                'label' => __('Media library'),
                'value' => 'media_library'
            ],
            [
                'label' => __('External link'),
                'value' => 'external_link'
            ]
        ];
    }
}
