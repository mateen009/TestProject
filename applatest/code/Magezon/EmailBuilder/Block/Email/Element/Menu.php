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

namespace Magezon\EmailBuilder\Block\Email\Element;

class Menu extends \Magezon\SimpleBuilder\Block\Element
{
    /**
     * @return array
     */
    public function getMenus()
    {
        $element = $this->getElement();
        $menus = $element->getMenuItems();

        return $menus;
    }

    /**
     * @return string
     */
    public function getAdditionalStyleHtml()
    {
        $element = $this->getElement();

        $styles1['background-color'] = $this->getStyleColor($element->getData('background_color'));
        $styleHtml = $this->getStyles('.mgz-email-navbar', $styles1);

        $styles2 = [
            'font-size' => $this->getStyleProperty($element->getData('a_font_size')),
            'color' => $this->getStyleColor($element->getData('a_color')),
            'border-radius' => $this->getStyleProperty($element->getData('a_border_radius')),
            'text-decoration' => 'none',
            'padding' => '15px 20px',
            'margin' => '0 5px'
        ];
        $styleHtml .= $this->getStyles('.mgz-email-navbar a', $styles2);

        $styles3 = [
            'background-color' => $this->getStyleColor($element->getData('a_background_color_hover')),
            'color' => $this->getStyleColor($element->getData('a_color_hover')) .'!important',
            'font-size' => $this->getStyleProperty($element->getData('a_font_size_hover')),
        ];
        $styleHtml .= $this->getStyles('.mgz-email-navbar a:hover', $styles3);

        return $styleHtml;
    }
}
