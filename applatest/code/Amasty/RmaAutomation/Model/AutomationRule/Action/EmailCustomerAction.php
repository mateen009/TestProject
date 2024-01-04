<?php

namespace Amasty\RmaAutomation\Model\AutomationRule\Action;

use Amasty\RmaAutomation\Api\PerformActionInterface;
use Amasty\RmaAutomation\Model\RegistryActions;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class EmailCustomerAction
 */
class EmailCustomerAction implements PerformActionInterface
{
    /**
     * @var \Amasty\Rma\Utils\Email
     */
    private $email;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

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
     * @param \Amasty\Rma\Utils\Email $email
     * @param CustomerRepositoryInterface $customerRepository
     * @param $value
     * @param array $additional_data
     */
    public function __construct(
        \Amasty\Rma\Utils\Email $email,
        CustomerRepositoryInterface $customerRepository,
        $value,
        $additional_data = []
    ) {
        $this->email = $email;
        $this->value = $value;
        $this->additionalData = $additional_data;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @inheritdoc
     */
    public function perform(\Amasty\Rma\Api\Data\RequestInterface $request)
    {
        if (!$this->value || !$request->getCustomerId()) {
            return;
        }
        $sender = $this->additionalData[RegistryActions::EMAIL_CUSTOMER_SENDER];
        $templateId = $this->additionalData[RegistryActions::EMAIL_CUSTOMER_TEMPLATE];

        try {
            $email = $this->customerRepository->getById($request->getCustomerId())->getEmail();

            $this->email->sendEmail(
                $email,
                0,
                $templateId,
                [],
                \Magento\Framework\App\Area::AREA_FRONTEND,
                $sender
            );
        } catch (LocalizedException $e) {
            return;
        }
    }
}
