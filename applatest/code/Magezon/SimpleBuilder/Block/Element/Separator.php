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

class Separator extends \Magezon\SimpleBuilder\Block\Element
{
	/**
	 * @return string
	 */
	public function getAdditionalStyleHtml()
	{
		$element = $this->getElement();
		$styles['border-color']     = $this->getStyleColor($element->getData('color'));
		$styles['border-top-style'] = $this->getStyleProperty($element->getData('style'));
		if ($element->getData('border_width')) {
			$styles['border-top-width'] = $this->getStyleProperty($element->getData('border_width'));
		}
		if ($element->getData('line_weight')) {
			$styles['border-top-width'] = $this->getStyleProperty($element->getData('line_weight'));
		}
		$styleHtml = $this->getStyles('.mgz-element-separator-line', $styles);
		$styleHtml .= $this->getLineStyles();
		return $styleHtml;
	}
}