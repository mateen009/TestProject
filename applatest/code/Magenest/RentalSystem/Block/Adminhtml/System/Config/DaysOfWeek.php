<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class DaysOfWeek extends Field
{
    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $output = [];
        $values = $element->getValue() ? explode(',', $element->getValue()) : [];
        foreach ($values as $key => $value) {
            $copy = clone $element;
            $copy->setId($copy->getId() . "_" . $key)
                ->setStyle('width:50px;')
                ->setName($element->getName() . '[]');
            $output[] = $copy->setValue($value ?? null)->getElementHtml();
        }

        return implode(" ", $output);
    }
}
