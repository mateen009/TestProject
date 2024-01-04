<?php
namespace Cminds\Oapm\Helper;

use Cminds\Oapm\Model\Config\Source\Approver;
use Cminds\Oapm\Model\Payment\Oapm;
use Magento\Customer\Model\GroupRegistry;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Config extends AbstractHelper
{
    /**
     * Payment method configuration field codes.
     */
    const FIELD_ACTIVE_CODE = 'active';
    const FIELD_SANDBOX_CODE = 'sandbox';
    const FIELD_DEBUG_CODE = 'debug';
    const FIELD_TITLE_CODE = 'title';
    const FIELD_REMINDER_INTERVALS_CODE = 'reminder_intervals';
    const FIELD_ORDER_LIFETIME_CODE = 'order_lifetime';
    const FIELD_USE_GROUP_MANAGER_EMAIL = 'use_group_manager_email';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var bool
     */
    protected $isEnabled;

    /**
     * @var bool
     */
    protected $isSandboxEnabled;

    /**
     * @var bool
     */
    protected $isDebugEnabled;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var array
     */
    protected $reminderIntervals;

    /**
     * @var int
     */
    protected $orderLifetime;

    /**
     * @var CustomerSession $customerSession
     */
    protected $customerSession;

    /**
     * @var GroupRegistry
     */
    protected $groupRegistry;

    /**
     * Config constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param CustomerSession $customerSession
     * @param GroupRegistry $groupRegistry
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        CustomerSession $customerSession,
        GroupRegistry $groupRegistry
    ) {
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->groupRegistry = $groupRegistry;
        parent::__construct($context);
    }

    /**
     * Return bool value if payment method is enabled or not.
     *
     * @return bool
     */
    public function isEnabled()
    {
        if (is_null($this->isEnabled)) {
            $this->isEnabled = (bool)$this->getConfigData(self::FIELD_ACTIVE_CODE);
        }

        return $this->isEnabled;
    }


    /**
     * Return bool value if use group manager email method is enabled or not.
     *
     * @return bool
     */
    public function useGroupManagerEmail()
    {
        if (is_null($this->isEnabled)) {
            $this->isEnabled = (bool)$this->getConfigData(self::FIELD_USE_GROUP_MANAGER_EMAIL);
        }

        return $this->isEnabled;
    }

    /**
     * Return bool value if payment method sandbox is enabled or not.
     *
     * @return bool
     */
    public function isSandboxEnabled()
    {
        if (is_null($this->isSandboxEnabled)) {
            $this->isSandboxEnabled = (bool)$this->getConfigData(self::FIELD_SANDBOX_CODE);
        }

        return $this->isSandboxEnabled;
    }

    /**
     * Return bool value if payment method debug is enabled or not.
     *
     * @return bool
     */
    public function isDebugEnabled()
    {
        if (is_null($this->isDebugEnabled)) {
            $this->isDebugEnabled = (bool)$this->getConfigData(self::FIELD_DEBUG_CODE);
        }

        return $this->isDebugEnabled;
    }

    /**
     * Return payment method title.
     *
     * @return string
     */
    public function getTitle()
    {
        if (is_null($this->title)) {
            $this->title = $this->getConfigData(self::FIELD_TITLE_CODE);
        }

        return $this->title;
    }

    /**
     * Return payment method reminder intervals.
     *
     * @return array
     */
    public function getReminderIntervals()
    {
        if (is_null($this->orderLifetime)) {
            $intervals = $this->getConfigData(self::FIELD_REMINDER_INTERVALS_CODE);
            $intervals = ! empty($intervals) ? explode(',', $intervals) : [];

            $intervals = array_map('intval', $intervals);
            asort($intervals);

            $this->reminderIntervals = $intervals;
        }

        return $this->reminderIntervals;
    }

    /**
     * Return payment method order lifetime.
     *
     * @return bool
     */
    public function getOrderLifetime()
    {
        if (is_null($this->orderLifetime)) {
            $this->orderLifetime = (int)$this->getConfigData(self::FIELD_ORDER_LIFETIME_CODE);
        }

        return $this->orderLifetime;
    }

    /**
     * Retrieve information from payment method configuration.
     *
     * @param string $field
     * @param int|string|null|Mage_Core_Model_Store $storeId
     *
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        $path = sprintf('payment/%s/%s', $this->getCode(), $field);

        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve payment method code.
     *
     * @return string
     */
    public function getCode()
    {
        return Oapm::METHOD_CODE;
    }

    /**
     * Retrieve admin approver code.
     *
     * @return string
     */
    public function getAdminAprover()
    {
        return Approver::APPROVER_ADMIN;
    }

    /**
     * Return bool value which indicates if order lifetime is unlimited or not.
     *
     * @return bool
     */
    public function isOrderLifetimeUnlimited()
    {
        return $this->getOrderLifetime() === 0;
    }

    public function getAdminSenderEmail()
    {
        return $this->scopeConfig->getValue(
            'trans_email/ident_' . $this->getConfigData('approver_identity') . '/email'
        );
    }

    public function getAdminSenderName()
    {
        return $this->scopeConfig->getValue(
            'trans_email/ident_' . $this->getConfigData('approver_identity') . '/name'
        );
    }

    /**
     * @return string|null
     */
    public function checkCustomerGroupManagerEmail()
    {
        $groupId = $this->customerSession->getCustomerGroupId();

        if (null !== $groupId) {
            try {
                $groupData = $this->groupRegistry->retrieve($groupId);
            } catch (LocalizedException $exception) {
                return null;
            }

            return !empty($groupData->getData('group_manager_email'))
                ? $groupData->getData('group_manager_email')
                : null;
        }

        return null;
    }
}
