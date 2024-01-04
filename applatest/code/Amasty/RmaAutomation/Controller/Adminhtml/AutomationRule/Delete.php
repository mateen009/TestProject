<?php

namespace Amasty\RmaAutomation\Controller\Adminhtml\AutomationRule;

use Amasty\RmaAutomation\Api\AutomationRuleRepositoryInterface;
use Amasty\RmaAutomation\Api\Data\AutomationRuleInterface;
use Amasty\RmaAutomation\Controller\Adminhtml\AbstractAutomationRule;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Class Delete
 */
class Delete extends AbstractAutomationRule
{
    /**
     * @var AutomationRuleRepositoryInterface
     */
    private $repository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Action\Context $context
     * @param AutomationRuleRepositoryInterface $repository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        AutomationRuleRepositoryInterface $repository,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->logger = $logger;
    }

    /**
     * Delete action
     *
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function execute()
    {
        if ($id = (int)$this->getRequest()->getParam(AutomationRuleInterface::RULE_ID)) {
            try {
                $this->repository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('The rule has been deleted.'));
                $this->_redirect('amrmaaut/*/');

                return;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Can\'t delete rule right now. Please review the log and try again.')
                );
                $this->logger->critical($e);
            }
            $this->_redirect('amrmaaut/*/edit', [AutomationRuleInterface::RULE_ID => $id]);

            return;
        } else {
            $this->messageManager->addErrorMessage(__('Can\'t find a rule to delete.'));
        }

        $this->_redirect('amrmaaut/*/');
    }
}
