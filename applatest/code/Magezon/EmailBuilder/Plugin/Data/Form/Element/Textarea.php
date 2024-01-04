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
 * @package   Magezon_EmailBuilder
 * @copyright Copyright (C) 2020 Magezon (https://www.magezon.com)
 */

namespace Magezon\EmailBuilder\Plugin\Data\Form\Element;

class Textarea
{
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    public function __construct(
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * @param $subject
     * @param $result
     * @return string
     */
    public function afterGetElementHtml($subject, $result)
    {
        $regex  = '@(?:<textarea id="template_text")(.*)</textarea>@msU';
        preg_match_all($regex, $result, $_matches);
        if (count($_matches[0])) {
            $result = $_matches[0][0];
            $id = time() . uniqid();
            $block = $this->layoutFactory->create()->createBlock(\Magezon\EmailBuilder\Block\Builder::class);
            $data['html_id'] = 'magezon' . $id;
            $data['target_id'] = $subject->getHtmlId();
            $block->setData($data);
            $result .= $block->toHtml();
        }

        return $result;
    }
}
