<?php
namespace Magenest\RentalSystem\Controller\Adminhtml\Rule;

class Delete extends AbstractRule
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        $ruleId = $this->getRequest()->getParam('id');
        if ($ruleId) {
            try {
                $rule = $this->rentalRule->create();
                $this->rentalRuleResource->load($rule, $ruleId)->delete($rule);
                $this->messageManager->addSuccessMessage(__('You deleted the rule.'));
                $this->_redirect('rentalsystem/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->logger->critical($e->getMessage(), ['trace' => $e->getTraceAsString()]);
                $this->_redirect('rentalsystem/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }

        $this->messageManager->addErrorMessage(__('Missing required parameter(s).'));
        $this->_redirect('rentalsystem/*/');
    }
}
