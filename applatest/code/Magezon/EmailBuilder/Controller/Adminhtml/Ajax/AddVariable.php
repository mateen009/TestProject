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
 * @package   Magezon_
 * @copyright Copyright (C) 2020 Magezon (https://magezon.com)
 */

namespace Magezon\EmailBuilder\Controller\Adminhtml\Ajax;

class AddVariable extends \Magento\Backend\App\Action
{
    /**
     * @return bool|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        $variable = $post['variable'];
        if ($variable) {
            $this->getResponse()->setBody($this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)
                ->jsonEncode($variable));
        } else {
            return false;
        }
    }
}
