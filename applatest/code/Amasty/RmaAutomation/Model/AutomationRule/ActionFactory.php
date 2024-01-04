<?php

namespace Amasty\RmaAutomation\Model\AutomationRule;

use Amasty\RmaAutomation\Model\RegistryActions;

/**
 * Class ActionFactory
 */
class ActionFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * Instance names to create
     *
     * @var array
     */
    protected $instanceNames = [];

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceNames = [
            RegistryActions::STATUS_ACTION => Action\StatusAction::class,
            RegistryActions::OWNER_ACTION => Action\OwnerAction::class,
            RegistryActions::EMAIL_ADMIN_ACTION => Action\EmailAdminAction::class,
            RegistryActions::EMAIL_CUSTOMER_ACTION => Action\EmailCustomerAction::class
        ]
    ) {
        $this->objectManager = $objectManager;
        $this->instanceNames = $instanceNames;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param string $type
     * @param array $data
     *
     * @return \Amasty\RmaAutomation\Api\PerformActionInterface|bool
     */
    public function create($type, array $data = [])
    {
        if (!isset($this->instanceNames[$type])) {
            return false;
        }

        return $this->objectManager->create($this->instanceNames[$type], $data);
    }
}
