<?php

namespace Amasty\RmaAutomation\Model\AutomationRule;

use Amasty\Rma\Api\Data\RequestInterface;
use Amasty\RmaAutomation\Api\AutomationRuleRepositoryInterface;
use Amasty\RmaAutomation\Api\Data\RuleActionInterface;
use Amasty\RmaAutomation\Model\AutomationRule\ResourceModel\CollectionFactory;
use Magento\Framework\Json\Helper\Data;

/**
 * Class AutomationRuleProcessor
 */
class AutomationRuleProcessor
{
    const PROCESS_NEW = 1;
    const PROCESS_EXISTING = 2;

    /**
     * @var AutomationRuleRepositoryInterface
     */
    private $repository;

    /**
     * @var \Amasty\Rma\Api\RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ActionFactory
     */
    private $actionFactory;

    /**
     * @var Data
     */
    private $jsonHelper;

    /**
     * @var \Amasty\Rma\Model\Request\ResourceModel\CollectionFactory
     */
    private $requestCollectionFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Amasty\Rma\Model\OptionSource\Manager
     */
    private $managerOptions;

    /**
     * @var \Amasty\RmaAutomation\Model\ConfigProvider
     */
    private $configProvider;

    public function __construct(
        AutomationRuleRepositoryInterface $repository,
        \Amasty\Rma\Api\RequestRepositoryInterface $requestRepository,
        CollectionFactory $collectionFactory,
        \Amasty\Rma\Model\Request\ResourceModel\CollectionFactory $requestCollectionFactory,
        ActionFactory $actionFactory,
        Data $jsonHelper,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Amasty\Rma\Model\OptionSource\Manager $managerOptions,
        \Amasty\RmaAutomation\Model\ConfigProvider $configProvider
    ) {
        $this->repository = $repository;
        $this->requestRepository = $requestRepository;
        $this->collectionFactory = $collectionFactory;
        $this->actionFactory = $actionFactory;
        $this->jsonHelper = $jsonHelper;
        $this->requestCollectionFactory = $requestCollectionFactory;
        $this->eventManager = $eventManager;
        $this->managerOptions = $managerOptions;
        $this->configProvider = $configProvider;
    }

    /**
     * @param \Amasty\Rma\Api\Data\RequestInterface $request
     * @param int $rmaType
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function processRma($request, $rmaType = null)
    {
        if (!$this->configProvider->isEnabled()) {
            return;
        }
        $rules = $this->repository->getActiveRules($rmaType);
        $ruleToApplyIds = [];

        foreach ($rules as $rule) {
            if ($rule->getConditions()->validate($request)) {
                $ruleToApplyIds[] = $rule->getRuleId();

                if ($rule->isStopFurtherProcessing()) {
                    break;
                }
            }
        }

        $this->performActions($ruleToApplyIds, $request);
    }

    /**
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function processAllRma()
    {
        /** @var \Amasty\Rma\Model\Request\ResourceModel\Collection $requestCollection */
        $requestCollection = $this->requestCollectionFactory->create();
        $requestCollection->addFieldToSelect(RequestInterface::REQUEST_ID);

        foreach ($requestCollection->getData() as $requestData) {
            $this->processRma(
                $this->requestRepository->getById(
                    $requestData[RequestInterface::REQUEST_ID]
                ),
                self::PROCESS_EXISTING
            );
        }
    }

    /**
     * @param array $ruleToApplyIds
     * @param \Amasty\Rma\Api\Data\RequestInterface $request
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    private function performActions($ruleToApplyIds, $request)
    {
        $request = $this->requestRepository->getById($request->getRequestId());

        foreach ($ruleToApplyIds as $ruleId) {
            foreach ($this->repository->getActionsByRuleId($ruleId) as $actionData) {
                $actionType = $actionData[RuleActionInterface::TYPE];
                $data = [
                    RuleActionInterface::VALUE => $actionData[RuleActionInterface::VALUE],
                    RuleActionInterface::ADDITIONAL_DATA => $this->jsonHelper->jsonDecode(
                        $actionData[RuleActionInterface::ADDITIONAL_DATA]
                    )
                ];
                $action = $this->actionFactory->create($actionType, $data);

                if ($action) {
                    $action->perform($request);
                }
            }
        }
        $this->requestRepository->save($request);
        $this->dispatchEvents($request);
    }

    /**
     * @param \Amasty\Rma\Api\Data\RequestInterface $request
     */
    private function dispatchEvents($request)
    {
        if ((int)$request->getData(RequestInterface::STATUS)
            !== (int)$request->getOrigData(RequestInterface::STATUS)
        ) {
            $this->eventManager->dispatch(
                \Amasty\Rma\Observer\RmaEventNames::STATUS_CHANGED_BY_SYSTEM,
                [
                    'request' => $request,
                    'from' => (int)$request->getOrigData(RequestInterface::STATUS),
                    'to' => (int)$request->getData(RequestInterface::STATUS)
                ]
            );
        }

        if ((int)$request->getData(RequestInterface::MANAGER_ID)
            !== (int)$request->getOrigData(RequestInterface::MANAGER_ID)
        ) {
            $this->eventManager->dispatch(
                \Amasty\Rma\Observer\RmaEventNames::MANAGER_CHANGED_BY_SYSTEM,
                [
                    'request' => $request,
                    'from' => $this->getManager((int)$request->getOrigData(RequestInterface::MANAGER_ID)),
                    'to' => $this->getManager((int)$request->getData(RequestInterface::MANAGER_ID))
                ]
            );
        }
    }

    /**
     * @param int $managerId
     *
     * @return string
     */
    private function getManager($managerId)
    {
        $managers = $this->managerOptions->toArray();
        $manager = __('Unknown');

        if (isset($managers[$managerId])) {
            $manager = $managers[$managerId];
        }

        return $manager;
    }
}
