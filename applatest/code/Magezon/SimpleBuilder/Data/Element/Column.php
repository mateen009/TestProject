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

class Column extends AbstractElement
{
    /**
     * @return Magezon\Builder\Data\Form\Element\Fieldset
     */
    public function prepareGeneralTab()
    {
    	$general = parent::prepareGeneralTab();

	    	$general->addChildren(
	            'md_size',
	            'select',
	            [
	                'key'             => 'md_size',
	                'sortOrder'       => 10,
	                'defaultValue'    => 12,
	                'templateOptions' => [
	                    'label'   => 'Width',
	                    'options' => $this->builderHelper->getResizableSizes(),
	                    'note'    => __('Select column width.')
	                ]
	            ]
	        );

    	return $general;
    }
}