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

class Copyright extends \Magezon\SimpleBuilder\Block\Element
{
    /**
     * Get Email Copyright
     *
     * @return mixed
     */
    public function getCopyright()
    {
        return $this->getElement()->getEmailCopyright();
    }

    /**
     * @return string
     */
    public function getAdditionalStyleHtml()
    {
        $element = $this->getElement();
        $styles = [
            'font-size' => $this->getStyleProperty($element->getData('font_size')),
            'color' => $this->getStyleColor($element->getData('color')),
            'line-height' => $element->getData('line_height'),
            'font-weight' => $element->getData('font_weight')
        ];
        $styleHtml = $this->getStyles('.email-builder-copyright', $styles);
        return $styleHtml;
    }
}
