<?php

namespace Amasty\RmaAutomation\Controller\Adminhtml\AutomationRule;

use Amasty\RmaAutomation\Controller\Adminhtml\AbstractAutomationRule;

/**
 * Class Create
 */
class Create extends AbstractAutomationRule
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
