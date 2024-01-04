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

class SingleImage extends AbstractElement
{
    /**
     * @return Magezon\Builder\Data\Form\Element\Fieldset
     */
    public function prepareGeneralTab()
    {
    	$general = parent::prepareGeneralTab();

    	$general->addChildren(
            'image',
            'image',
            [
				'sortOrder'       => 10,
				'key'             => 'image',
				'defaultValue'    => 'mgzbuilder/no_image.png',
				'templateOptions' => [
					'label' => __('Image')
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
	            'image_width',
	            'text',
	            [
					'sortOrder'       => 10,
					'key'             => 'image_width',
					'templateOptions' => [
						'label' => __('Image Width')
	                ]
	            ]
	        );

	        $container2->addChildren(
	            'image_height',
	            'text',
	            [
					'sortOrder'       => 20,
					'key'             => 'image_height',
					'templateOptions' => [
						'label' => __('Image Height')
	                ]
	            ]
	        );

    	$general->addChildren(
            'link',
            'link',
            [
				'sortOrder'       => 30,
				'key'             => 'link',
				'templateOptions' => [
					'label' => __('Custom link')
                ]
            ]
        );

    	return $general;
    }
}