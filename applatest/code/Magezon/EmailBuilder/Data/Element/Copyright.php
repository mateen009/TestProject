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

class Copyright extends \Magezon\SimpleBuilder\Data\Element\AbstractElement
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
                'email_copyright',
                'text',
                [
                    'sortOrder'       => 10,
                    'key'             => 'email_copyright',
                    'defaultValue'    => 'COPYRIGHT ©2020 Magezon Themes Email, INC. ALL RIGHTS RESERVED',
                    'templateOptions' => [
                        'label' => __('Content')
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

        $container2->addChildren(
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

        $container3 = $general->addContainerGroup(
            'container3',
            [
                'sortOrder' => 30
            ]
        );

        $container3->addChildren(
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

        $container3->addChildren(
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

        return $general;
    }
}
