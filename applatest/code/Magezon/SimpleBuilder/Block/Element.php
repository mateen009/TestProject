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

namespace Magezon\SimpleBuilder\Block;

use \Magento\Framework\App\ObjectManager;

class Element extends \Magezon\Builder\Block\Element
{
    public function getStylesHtml()
    {
        $dataHelper = $this->getDataHelper();
        $element    = $this->getElement();
        $html = '';

        $styles = [];
        $styles['float'] = $element->getData('el_float');
        $_html = $dataHelper->parseStyles($styles);
        if ($_html) {
            $html .= '.' . $element->getHtmlId() . '{';
            $html .= $_html;
            $html .= '}';
        }

        $styles = [];
        $styles['text-align'] = $element->getData('align');
        $styles['min-height'] = $dataHelper->getStyleProperty($element->getData('min_height'), true);

        $paddingTop    = $dataHelper->getStyleProperty($element->getData('padding_top'));
        $paddingRight  = $dataHelper->getStyleProperty($element->getData('padding_right'));
        $paddingBottom = $dataHelper->getStyleProperty($element->getData('padding_bottom'));
        $paddingLeft   = $dataHelper->getStyleProperty($element->getData('padding_left'));
        if (!$this->isNull($paddingTop) && !$this->isNull($paddingRight) && !$this->isNull($paddingBottom) && !$this->isNull($paddingLeft)) {
            if ($paddingTop == $paddingRight && $paddingTop == $paddingBottom && $paddingTop == $paddingLeft) {
                $styles['padding'] = $paddingTop . '!important';
            } else {
                $styles['padding'] = $paddingTop . ' ' . $paddingRight . ' ' . $paddingBottom . ' ' . $paddingLeft . '!important';
            }
        } else {
            $styles['padding-top']    = $dataHelper->getStyleProperty($element->getData('padding_top'), true);
            $styles['padding-right']  = $dataHelper->getStyleProperty($element->getData('padding_right'), true);
            $styles['padding-bottom'] = $dataHelper->getStyleProperty($element->getData('padding_bottom'), true);
            $styles['padding-left']   = $dataHelper->getStyleProperty($element->getData('padding_left'), true);
        }

        $marginTop    = $dataHelper->getStyleProperty($element->getData('margin_top'));
        $marginRight  = $dataHelper->getStyleProperty($element->getData('margin_right'));
        $marginBottom = $dataHelper->getStyleProperty($element->getData('margin_bottom'));
        $marginLeft   = $dataHelper->getStyleProperty($element->getData('margin_left'));
        if (!$this->isNull($marginTop) && !$this->isNull($marginRight) && !$this->isNull($marginBottom) && !$this->isNull($marginLeft)) {
            if ($marginTop == $marginRight && $marginTop == $marginBottom && $marginTop == $marginLeft) {
                $styles['margin'] = $marginTop . '!important';
            } else {
                $styles['margin'] = $marginTop . ' ' . $marginRight . ' ' . $marginBottom . ' ' . $marginLeft . '!important';
            }
        } else {
            $styles['margin-top']     = $dataHelper->getStyleProperty($element->getData('margin_top'), true);
            $styles['margin-right']   = $dataHelper->getStyleProperty($element->getData('margin_right'), true);
            $styles['margin-bottom']  = $dataHelper->getStyleProperty($element->getData('margin_bottom'), true);
            $styles['margin-left']    = $dataHelper->getStyleProperty($element->getData('margin_left'), true);
        }

        $borderStyle = $element->getData('border_style');
        if ($borderStyle && $element->getData('border_color')) {
            $borderTopWidth         = $dataHelper->getStyleProperty($element->getData('border_top_width'));
            $borderRightWidth       = $dataHelper->getStyleProperty($element->getData('border_right_width'));
            $borderBottomWidth      = $dataHelper->getStyleProperty($element->getData('border_bottom_width'));
            $borderLeftWidth        = $dataHelper->getStyleProperty($element->getData('border_left_width'));
            $borderColor            = $dataHelper->getStyleColor($element->getData('border_color'));
            $styles['border-color'] = $dataHelper->getStyleColor($element->getData('border_color'), true);

            if ($element->getData('border_top_width') != '') {
                $styles['border-top-width'] = $dataHelper->getStyleProperty($element->getData('border_top_width'), true);
                $styles['border-top-style'] = $borderStyle;
            }

            if ($element->getData('border_right_width') != '') {
                $styles['border-right-width'] = $dataHelper->getStyleProperty($element->getData('border_right_width'), true);
                $styles['border-right-style'] = $borderStyle;
            }

            if ($element->getData('border_bottom_width') != '') {
                $styles['border-bottom-width'] = $dataHelper->getStyleProperty($element->getData('border_bottom_width'), true);
                $styles['border-bottom-style'] = $borderStyle;
            }

            if ($element->getData('border_left_width') != '') {
                $styles['border-left-width'] = $dataHelper->getStyleProperty($element->getData('border_left_width'), true);
                $styles['border-left-style'] = $borderStyle;
            }

            if (isset($styles['border-top-width']) && isset($styles['border-right-width']) && isset($styles['border-bottom-style']) && isset($styles['border-left-width'])) {
                if ($borderTopWidth == $borderRightWidth && $borderTopWidth == $borderBottomWidth && $borderTopWidth == $borderLeftWidth) {
                    $styles['border'] = $borderTopWidth . ' ' . $borderStyle . ' ' . $borderColor . '!important';
                    unset($styles['border-top-width']);
                    unset($styles['border-top-style']);
                    unset($styles['border-right-width']);
                    unset($styles['border-right-style']);
                    unset($styles['border-bottom-width']);
                    unset($styles['border-bottom-style']);
                    unset($styles['border-left-width']);
                    unset($styles['border-left-style']);
                    unset($styles['border-color']);
                }
            }
        }

        $borderTopLeftRadius     = $dataHelper->getStyleProperty($element->getData('border_top_left_radius'));
        $borderTopRightRadius    = $dataHelper->getStyleProperty($element->getData('border_top_right_radius'));
        $borderBottomRightRadius = $dataHelper->getStyleProperty($element->getData('border_bottom_right_radius'));
        $borderBottomLeftRadius  = $dataHelper->getStyleProperty($element->getData('border_bottom_left_radius'));
        if ($borderTopLeftRadius!='' || $borderTopRightRadius!='' || $borderBottomRightRadius!='' || $borderBottomLeftRadius!='') {
            if ($borderTopLeftRadius == $borderTopRightRadius && $borderTopLeftRadius == $borderBottomRightRadius && $borderTopLeftRadius == $borderBottomLeftRadius) {
                $styles['border-radius'] = $borderTopLeftRadius . '!important';
            } else {
                if (!$borderTopLeftRadius) $borderTopLeftRadius = 0;
                if (!$borderTopRightRadius) $borderTopRightRadius = 0;
                if (!$borderBottomRightRadius) $borderBottomRightRadius = 0;
                if (!$borderBottomLeftRadius) $borderBottomLeftRadius = 0;
                $styles['border-radius'] = $borderTopLeftRadius . ' ' . $borderTopRightRadius . ' ' . $borderBottomRightRadius . ' ' . $borderBottomLeftRadius . '!important';
            }
        }

        $styles['background-color'] = $dataHelper->getStyleColor($element->getData('background_color'));
        $backgroundImage = $element->getData('background_image');
        if ($backgroundImage) {
            $backgroundStyle = $element->getData('background_style');
            $styles['background-image'] = 'url(\'' . $dataHelper->getImageUrl($backgroundImage) . '\')';
            switch ($backgroundStyle) {
                case 'cover':
                case 'contain':
                $styles['background-size'] = $element['background_style'];
                break;

                case 'full-width':
                $styles['background-size'] = '100% auto';
                break;

                case 'full-height':
                $styles['background-size'] = 'auto 100%';
                break;

                case 'repeat-x':
                $styles['background-repeat'] = 'repeat-x';
                break;

                case 'repeat-y':
                $styles['background-repeat'] = 'repeat-y';
                break;

                case 'no-repeat':
                case 'repeat':
                $styles['background-repeat'] = $element['background_style'];
                break;

                default:
                $styles['background-size'] = $backgroundStyle;
                break;
            }
            $backgroundPosition = $element->getData('background_position');
            if ($backgroundPosition == 'custom') {
                $backgroundPosition = $element->getData('custom_background_position');
            } else {
                $backgroundPosition = str_replace('-', ' ', $backgroundPosition);
            }
            if ($backgroundPosition) {
                $styles['background-position'] = $backgroundPosition;
            }
        }

        if ($_html = $dataHelper->parseStyles($styles)) {
            $html .= '.' . $element->getStyleHtmlId() . '{';
            $html .= $_html;
            $html .= '}';
        }
        $html .= $this->getAdditionalStyleHtml();
        if ($html) $html = '<style class="mgz-style">' . $html . '</style>';
        return $html;
    }

    /**
     * @return \Magezon\Builder\Data\Elements
     */
    public function getElementsManager()
    {
    	if ($this->_elementsManager==NULL) {
    		$this->_elementsManager = ObjectManager::getInstance()->get(\Magezon\SimpleBuilder\Data\Elements::class);
	    }
	    return $this->_elementsManager;
    }
}