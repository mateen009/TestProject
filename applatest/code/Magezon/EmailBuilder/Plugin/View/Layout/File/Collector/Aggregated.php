<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_EmailBuilder
 * @copyright Copyright (C) 2020 Magezon (https://www.magezon.com)
 */

namespace Magezon\EmailBuilder\Plugin\View\Layout\File\Collector;

class Aggregated
{
    /**
     * @var \Magezon\EmailBuilder\Helper\Data
     */
    protected $dataHelper;

    /**
     * @param \Magezon\EmailBuilder\Helper\Data $dataHelper
     */
    public function __construct(
        \Magezon\EmailBuilder\Helper\Data $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterGetFiles(
        $subject,
        $result
    ) {
        if (!$this->dataHelper->isModuleEnable()) {
            foreach ($result as $k => $file) {
                if (strpos($file->getFilename(), 'Magezon/EmailBuilder') !== false) {
                    unset($result[$k]);
                }
            }
        }
        return $result;
    }
}
