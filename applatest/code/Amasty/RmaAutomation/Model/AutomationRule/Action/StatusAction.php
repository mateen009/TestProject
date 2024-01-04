<?php

namespace Amasty\RmaAutomation\Model\AutomationRule\Action;

use Amasty\RmaAutomation\Api\PerformActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class StatusAction
 */
class StatusAction implements PerformActionInterface
{
    /**
     * Action value
     *
     * @var string
     */
    private $value;

    /**
     * Action additional data
     *
     * @var array
     */
    private $additionalData;

    /**
     * @var \Amasty\Rma\Api\StatusRepositoryInterface
     */
    private $statusRepository;

    /**
     * @param \Amasty\Rma\Api\StatusRepositoryInterface $statusRepository
     * @param $value
     * @param array $additional_data
     */
    public function __construct(
        \Amasty\Rma\Api\StatusRepositoryInterface $statusRepository,
        $value,
        $additional_data = []
    ) {
        $this->value = $value;
        $this->additionalData = $additional_data;
        $this->statusRepository = $statusRepository;
    }

    /**
     * @inheritdoc
     */
    public function perform(\Amasty\Rma\Api\Data\RequestInterface $request)
    {
        try {
            $this->statusRepository->getById($this->value);
        } catch (NoSuchEntityException $e) {
            return;
        }
        $request->setStatus($this->value);
    }
}
