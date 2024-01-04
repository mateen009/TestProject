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

namespace Magezon\EmailBuilder\Controller\Adminhtml\Ajax;

use Magento\Backend\App\Action;

class LoadTemplate extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Email\Model\Template\Config
     */
    private $emailConfig;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * LoadTemplate constructor.
     * @param Action\Context $context
     * @param \Magento\Email\Model\Template\Config $emailConfig
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        Action\Context $context,
        \Magento\Email\Model\Template\Config $emailConfig,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        parent::__construct($context);
        $this->emailConfig = $emailConfig;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        $templateId = $post['template_id'];
        $template = $this->_objectManager->create(\Magento\Email\Model\BackendTemplate::class);

        try {
            $parts = $this->emailConfig->parseTemplateIdParts($templateId);
            $templateId = $parts['templateId'];
            $theme = $parts['theme'];
            if ($theme) {
                $template->setForcedTheme($templateId, $theme);
            }
            $template->setForcedArea($templateId);
            $template->loadDefault($templateId);
            $template->setData(
                'template_variables',
                $this->jsonHelper->jsonEncode($template->getVariablesOptionArray(true))
            );
            $result = [
                'content' => $this->emailContent($template->getTemplateText()),
                'variables' => $template->getTemplateVariables()
            ];
            $this->getResponse()->setBody($this->jsonHelper->jsonEncode($result));
        } catch (\Exception $e) {
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
        }
    }

    /**
     * Get Email template content
     *
     * @param $templateText
     * @return mixed
     */
    private function emailContent($templateText)
    {
        $str1 = str_replace('{{template config_path="design/email/header_template"}}', '', $templateText);
        $str2 = str_replace('{{template config_path="design/email/footer_template"}}', '', $str1);

        return trim($str2);
    }
}
