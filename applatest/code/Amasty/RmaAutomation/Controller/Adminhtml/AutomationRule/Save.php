<?php

namespace Amasty\RmaAutomation\Controller\Adminhtml\AutomationRule;

use Amasty\RmaAutomation\Api\AutomationRuleRepositoryInterface;
use Amasty\RmaAutomation\Api\Data\AutomationRuleInterface;
use Amasty\RmaAutomation\Api\Data\RuleActionInterface;
use Amasty\RmaAutomation\Controller\Adminhtml\AbstractAutomationRule;
use Amasty\RmaAutomation\Model\RegistryActions;
use Magento\Backend\App\Action;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;

/**
 * Class Save
 */
class Save extends AbstractAutomationRule
{
    /**
     * @var AutomationRuleRepositoryInterface
     */
    private $repository;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var DataObject
     */
    private $dataObject;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RegistryActions
     */
    private $registryActions;

    /**
     * @var Data
     */
    private $jsonHelper;

    /**
     * @param AutomationRuleRepositoryInterface $repository
     * @param Registry $coreRegistry
     * @param DataPersistorInterface $dataPersistor
     * @param DataObject $dataObject
     * @param LoggerInterface $logger
     * @param Action\Context $context
     */
    public function __construct(
        AutomationRuleRepositoryInterface $repository,
        RegistryActions $registryActions,
        Registry $coreRegistry,
        DataPersistorInterface $dataPersistor,
        DataObject $dataObject,
        LoggerInterface $logger,
        Data $jsonHelper,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->registryActions = $registryActions;
        $this->coreRegistry = $coreRegistry;
        $this->dataPersistor = $dataPersistor;
        $this->dataObject = $dataObject;
        $this->logger = $logger;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        if ($data = $this->getRequest()->getPostValue()) {
            try {
                if ($id = (int)$this->getRequest()->getParam(AutomationRuleInterface::RULE_ID)) {
                    $model = $this->repository->getById($id);
                } else {
                    $model = $this->repository->getEmptyRuleModel();
                }
                $validateResult = $model->validateData($this->dataObject->addData($data));

                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $this->messageManager->addErrorMessage($errorMessage);
                    }

                    return $this->saveFormDataAndRedirect($data, $model->getId());
                }
                $this->saveRuleModel($model, $data);

                if ($this->getRequest()->getParam('back')) {
                    return $this->_redirect('amrmaaut/*/edit', [AutomationRuleInterface::RULE_ID => $model->getId()]);
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $this->saveFormDataAndRedirect($data, (int)$id);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the rule data. Please review the error log.')
                );
                $this->logError($e, $data, $id);

                return $this->saveFormDataAndRedirect($data, (int)$id);
            }
        }

        return $this->_redirect('amrmaaut/*/');
    }

    /**
     * @param AutomationRuleInterface $model
     * @param array $data
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    private function saveRuleModel($model, &$data)
    {
        if (isset($data['rule']['conditions'])) {
            $data['conditions'] = $data['rule']['conditions'];
        }
        unset($data['rule']);

        $model->loadPost($data);
        $model->setRuleActions($this->getRuleActions($data));
        $this->repository->save($model);

        $this->messageManager->addSuccessMessage(__('The rule is saved.'));
        $this->dataPersistor->clear('amrmaaut_automationrule');
    }

    /**
     * @param array $data
     *
     * @return \Amasty\RmaAutomation\Api\Data\RuleActionInterface[]
     */
    private function getRuleActions($data)
    {
        $actions = [];

        foreach ($this->registryActions->getActionKeys() as $actionKey) {
            if (!isset($data[$actionKey]) || $data[$actionKey] === '' || $data[$actionKey] == -1) {
                continue;
            }
            $ruleAction = $this->repository->getEmptyRuleActionModel();
            $ruleAction->addData(
                [
                    RuleActionInterface::TYPE => $actionKey,
                    RuleActionInterface::VALUE => $data[$actionKey],
                    RuleActionInterface::ADDITIONAL_DATA => $this->prepareActionAdditionalData($actionKey, $data)
                ]
            );
            $actions[] = $ruleAction;
        }

        return $actions;
    }

    /**
     * @param string $actionKey
     * @param array $data
     *
     * @return string
     */
    private function prepareActionAdditionalData($actionKey, $data)
    {
        $addDataKeys = $this->registryActions->getAdditionalDataByKey($actionKey);
        $additionalData = [];

        foreach ($addDataKeys as $addDataKey) {
            if (!isset($data[$addDataKey])) {
                continue;
            }
            $additionalData[$addDataKey] = $data[$addDataKey];
        }

        return $this->jsonHelper->jsonEncode($additionalData);
    }

    /**
     * @param \Exception $e
     * @param array $data
     * @param int $id
     */
    private function logError($e, $data, $id)
    {
        $this->logger->critical($e);
        $this->saveFormDataAndRedirect($data, $id);
    }

    /**
     * @param array $data
     * @param int|null $id
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    private function saveFormDataAndRedirect($data, $id)
    {
        $this->dataPersistor->set('amrmaaut_automationrule', $data);
        if (!empty($id)) {
            return $this->_redirect('amrmaaut/*/edit', [AutomationRuleInterface::RULE_ID => $id]);
        } else {
            return  $this->_redirect('amrmaaut/*/create');
        }
    }
}
