<?php

namespace Amasty\RmaAutomation\Controller\Adminhtml\AutomationRule;

use Amasty\RmaAutomation\Api\AutomationRuleRepositoryInterface;
use Amasty\RmaAutomation\Controller\Adminhtml\AbstractAutomationRule;
use Amasty\RmaAutomation\Model\AutomationRule\ResourceModel\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

/**
 * Class MassDelete
 */
class MassDelete extends AbstractAutomationRule
{
    /**
     * @var AutomationRuleRepositoryInterface
     */
    private $repository;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Action\Context $context
     * @param AutomationRuleRepositoryInterface $repository
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
     * @param LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        AutomationRuleRepositoryInterface $repository,
        CollectionFactory $collectionFactory,
        Filter $filter,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
        $this->logger = $logger;
    }

    /**
     * Mass action execution
     *
     * @throws LocalizedException
     */
    public function execute()
    {
        $this->filter->applySelectionOnTargetProvider();

        /** @var \Amasty\RmaAutomation\Model\AutomationRule\ResourceModel\Collection $collection */
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $deletedRules = 0;
        $failedRules = 0;

        if ($collection->count()) {
            foreach ($collection->getItems() as $rule) {
                try {
                    $this->repository->delete($rule);
                    $deletedRules++;
                } catch (LocalizedException $e) {
                    $failedRules++;
                } catch (\Exception $e) {
                    $this->logger->error(
                        __('Error occurred while deleting rule with ID %1. Error: %2'),
                        [$rule->getId(), $e->getMessage()]
                    );
                }
            }
        }

        if ($deletedRules !== 0) {
            $this->messageManager->addSuccessMessage(
                __('%1 rule(s) has been successfully deleted', $deletedRules)
            );
        }

        if ($failedRules !== 0) {
            $this->messageManager->addErrorMessage(
                __('%1 rule(s) has been failed to delete', $failedRules)
            );
        }

        $this->_redirect($this->_redirect->getRefererUrl());
    }
}
