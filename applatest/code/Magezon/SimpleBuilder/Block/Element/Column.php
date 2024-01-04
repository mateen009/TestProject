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

namespace Magezon\SimpleBuilder\Block\Element;

class Column extends \Magezon\SimpleBuilder\Block\Element
{
	/**
     * Get innwer classess
     * 
     * @return array
     */
	public function getInnerClasses()
	{
		$classes = parent::getInnerClasses();
		$classes[] = 'mgz-element-column-inner';
		return $classes;
	}
}