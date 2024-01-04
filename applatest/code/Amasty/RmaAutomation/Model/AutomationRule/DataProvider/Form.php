<?php

namespace Amasty\RmaAutomation\Model\AutomationRule\DataProvider;

use Amasty\RmaAutomation\Api\AutomationRuleRepositoryInterface;
use Amasty\RmaAutomation\Api\Data\AutomationRuleInterface;
use Amasty\RmaAutomation\Api\Data\RuleActionInterface;
use Amasty\RmaAutomation\Model\AutomationRule\ResourceModel\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Json\Helper\Data;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class Form
 */
class Form extends AbstractDataProvider
{
    /**
     * @var AutomationRuleRepositoryInterface
     */
    private $repository;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var array
     */
    private $loadedData;

    /**
     * @var Data
     */
    private $jsonHelper;

    public function __construct(
        AutomationRuleRepositoryInterface $repository,
        DataPersistorInterface $dataPersistor,
        RequestInterface $request,
        CollectionFactory $collectionFactory,
        Data $jsonHelper,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->repository = $repository;
        $this->dataPersistor = $dataPersistor;
        $this->request = $request;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        foreach ($this->collection->getData() as $rule) {
            $this->loadedData[$rule[AutomationRuleInterface::RULE_ID]] = $this->prepareRuleData($rule);
        }
        $data = $this->dataPersistor->get('amrmaaut_automationrule');

        if (!empty($data)) {
            $rule = $this->repository->getEmptyRuleModel();
            $rule->setData($data);
            $this->loadedData[$rule->getId()] = $rule->getData();
            $this->dataPersistor->clear('amrmaaut_automationrule');
        }

        return $this->loadedData;
    }

    /**
     * @param array $rule
     *
     * @return array
     */
    private function prepareRuleData($rule)
    {
        $actionsData = $this->repository->getActionsByRuleId($rule[AutomationRuleInterface::RULE_ID]);

        foreach ($actionsData as $actionData) {
            $rule[$actionData[RuleActionInterface::TYPE]] = $actionData[RuleActionInterface::VALUE];
            $additionalData = $this->jsonHelper->jsonDecode($actionData[RuleActionInterface::ADDITIONAL_DATA]);
            foreach ($additionalData as $key => $data) {
                $rule[$key] = $data;
            }
        }

        return $rule;
    }
}
