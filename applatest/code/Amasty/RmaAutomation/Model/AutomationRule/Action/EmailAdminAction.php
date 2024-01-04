<?php

namespace Amasty\RmaAutomation\Model\AutomationRule\Action;

use Amasty\RmaAutomation\Api\PerformActionInterface;
use Amasty\RmaAutomation\Model\RegistryActions;

/**
 * Class EmailAdminAction
 */
class EmailAdminAction implements PerformActionInterface
{
    /**
     * @var \Amasty\Rma\Utils\Email
     */
    private $email;

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
     * @param $value
     * @param array $additional_data
     */
    public function __construct(
        \Amasty\Rma\Utils\Email $email,
        $value,
        $additional_data = []
    ) {
        $this->value = $value;
        $this->additionalData = $additional_data;
        $this->email = $email;
    }

    /**
     * @inheritdoc
     */
    public function perform(\Amasty\Rma\Api\Data\RequestInterface $request)
    {
        if (!$this->value) {
            return;
        }
        $templateId = $this->additionalData[RegistryActions::EMAIL_ADMIN_TEMPLATE];
        $receivers = preg_split('/\n|\r\n?/', $this->additionalData[RegistryActions::EMAIL_ADMIN_RECEIVERS]);

        $this->email->sendEmail($receivers, 0, $templateId);
    }
}
