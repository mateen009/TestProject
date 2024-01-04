<?php
/**
 * Copyright © 2020 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Plugin;

/**
 * Class ExcludeMinification
 * @package Magenest\RentalSystem\Plugin
 */
class ExcludeMinification
{
    /**
     * Exclude external js from minification
     *
     * @param \Magento\Framework\View\Asset\Minification $subject
     * @param callable $proceed
     * @param string $contentType
     *
     * @return array
     */
    public function aroundGetExcludes(
        \Magento\Framework\View\Asset\Minification $subject,
        callable $proceed,
        $contentType
    ) {
        $result = $proceed($contentType);

        //Content type can be css or js
        if ($contentType == 'js') {
            $result[] = 'Magenest_RentalSystem/js/calendar.js';
            $result[] = 'Magenest_RentalSystem/js/custom.js';
            $result[] = 'Magenest_RentalSystem/js/daterangepicker.js';
            $result[] = 'Magenest_RentalSystem/js/form/element/delivery_type_options.js';
        }

        return $result;
    }
}