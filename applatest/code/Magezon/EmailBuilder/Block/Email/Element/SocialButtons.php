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

use Magento\Framework\View\Element\Template;

class SocialButtons extends \Magezon\SimpleBuilder\Block\Element
{
    /**
     * @var \Magezon\Builder\Helper\Data
     */
    protected $builderHelper;

    public function __construct(
        Template\Context $context,
        \Magezon\Builder\Helper\Data $builderHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->builderHelper      = $builderHelper;
    }

    /**
     * @return string
     */
    public function getMainText()
    {
        return $this->getElement()->getTextFollow();
    }

    /**
     * @return array
     */
    public function getSocials()
    {
        $element = $this->getElement();
        $socials = $element->getSocialItems();

        return $socials;
    }

    /**
     * @param $imageName
     * @return string
     */
    public function getSocialImg($imageName)
    {
        $image = $this->builderHelper->getImageUrl($imageName);
        return $image;
    }

    /**
     * @return string
     */
    public function getAdditionalStyleHtml()
    {
        $styleHtml = '';
        $element = $this->getElement();
        $color = $this->getStyleColor($element->getData('text_color'));
        $type = $element->getData('display_type');
        $displayText = $element->getData('text_display');

        if ($type == 'list') {
            $styles['display'] = 'inline-flex';
            $styles['min-height'] = '80px';
            $styleHtml .= $this->getStyles('.mgz-social-buttons', $styles);
        }

        if ($displayText) {
            $styles1 = [
                'font-size' => $this->getStyleProperty($element->getData('text_font_size')),
                'font-weight' => $element->getData('text_font_weight'),
                'color' => $color,
                'padding-top' => $this->getStyleProperty($element->getData('text_padding')),
                'padding-right' => ($type == 'list') ? '20px' : ''

            ];
            $styleHtml .= $this->getStyles('.mgz-social-buttons .mgz-social-text-follow', $styles1);
        }

        $styles2 = [
            'width' => $this->getStyleProperty($element->getData('img_width')),
            'height' => $this->getStyleProperty($element->getData('img_height')),
        ];
        if ($element->getData('img_border')) {
            $styles2['border'] = '1px solid ' .$color;
            $styles2['border-radius'] = '15px';
        }
        $styleHtml .= $this->getStyles('.mgz-social-buttons img', $styles2);

        $styles3 = [
            'opacity' => $element->getData('img_opacity')
        ];
        $styleHtml .= $this->getStyles('.mgz-social-buttons img:hover', $styles3);

        return $styleHtml;
    }
}
