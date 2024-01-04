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

class SiteLogo extends AbstractElement
{
    /**
     * @return Magezon\Builder\Data\Form\Element\Fieldset
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
		            'logo_width',
		            'number',
		            [
						'sortOrder'       => 10,
						'key'             => 'logo_width',
						'templateOptions' => [
							'label' => __('Logo Width')
		                ]
		            ]
		        );

		    	$container1->addChildren(
		            'logo_height',
		            'number',
		            [
						'sortOrder'       => 20,
						'key'             => 'logo_height',
						'templateOptions' => [
							'label' => __('Logo Height')
		                ]
		            ]
		        );

    	return $general;
    }
}