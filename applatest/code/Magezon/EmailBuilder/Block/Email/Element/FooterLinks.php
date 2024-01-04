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

namespace Magezon\EmailBuilder\Block\Email\Element;

class FooterLinks extends \Magezon\SimpleBuilder\Block\Element
{
    /**
     * @return mixed
     */
    public function getLinks()
    {
        $element = $this->getElement();
        $links = $element->getFooterLinks();

        return $links;
    }

    /**
     * @return string
     */
    public function getHeading()
    {
        $element = $this->getElement();
        $heading = $element->getData('heading_main_title');

        return $heading;
    }

    /**
     * @return string
     */
    public function getAdditionalStyleHtml()
    {
        $element = $this->getElement();
        $uppercase = $element->getData('title_uppercase');
        $styles = [
            'font-size' => $this->getStyleProperty($element->getData('title_font_size')),
            'color' => $this->getStyleColor($element->getData('title_color')),
            'text-transform' => ($uppercase) ? 'uppercase' : 'lowercase'
        ];
        $styleHtml = $this->getStyles('.footer-links-detail '.$this->getHeading(), $styles);

        $styles1 = [
            'padding' => '0',
        ];
        $styleHtml .= $this->getStyles('.footer-links-detail ul', $styles1);

        $styles2 = [
            'margin-left' => 0,
        ];
        $styleHtml .= $this->getStyles('.footer-links-detail ul > li', $styles2);

        $styles3 = [
            'font-size' => $this->getStyleProperty($element->getData('a_font_size')),
            'color' => $this->getStyleColor($element->getData('a_color')),
            'text-decoration' => 'none'
        ];
        $styleHtml .= $this->getStyles('.mgz-footer-links li a', $styles3);

        $styles4 = [
            'font-size' => $this->getStyleProperty($element->getData('a_hover_font_size')),
            'color' => $this->getStyleColor($element->getData('a_hover_color')) .'!important',
        ];
        $styleHtml .= $this->getStyles('.mgz-footer-links li a:hover', $styles4);

        return $styleHtml;
    }
}
