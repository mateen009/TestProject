<?php
/**
 * Copyright © 2020 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;

class Color extends Field
{
    /**
     * @param AbstractElement $element
     * @return string script
     * @throws LocalizedException
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = $element->getElementHtml();
        $js = $this->getLayout()->createBlock(ColorPickerJs::class)
            ->setFieldId($element->getHtmlId())
            ->setFieldValue($element->getData('value'))
            ->toHtml();

        return $html . $js;
    }
}
