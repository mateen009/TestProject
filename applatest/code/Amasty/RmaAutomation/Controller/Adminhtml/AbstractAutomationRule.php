<?php

namespace Amasty\RmaAutomation\Controller\Adminhtml;

/**
 * Class AbstractAutomationRule
 */
abstract class AbstractAutomationRule extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_RmaAutomation::automation_rules';
}
