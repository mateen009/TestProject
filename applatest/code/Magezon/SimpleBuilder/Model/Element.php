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

namespace Magezon\SimpleBuilder\Model;

class Element extends \Magezon\Builder\Model\Element
{
	/**
	 * @param \Magento\Framework\View\LayoutInterface $layout     
	 * @param \Magezon\Builder\Helper\Data            $dataHelper 
	 * @param \Magezon\Builder\Data\Elements          $elements   
	 */
	public function __construct(
        \Magento\Framework\View\LayoutInterface $layout,
		\Magezon\Builder\Helper\Data $dataHelper,
		\Magezon\Builder\Data\Elements $elements,
		\Magezon\SimpleBuilder\Data\Elements $builderPdfElements
	) {
		parent::__construct($layout, $dataHelper, $elements);
		$this->elements = $builderPdfElements;
	}

    public function getDefaultBlock()
    {
    	return '\Magezon\SimpleBuilder\Block\Element';
    }
}