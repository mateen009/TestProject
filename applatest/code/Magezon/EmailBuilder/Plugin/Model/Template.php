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

namespace Magezon\EmailBuilder\Plugin\Model;

class Template
{
    /**
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterGetProcessedTemplate($subject, $result)
    {
        $subject->deleteFileCreated('css/mgz-inline.css');
        return $result;
    }
}
