<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Observer\Config;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\Message\ManagerInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;

class Validate implements ObserverInterface
{
    const XML_PATH_WORK_HOURS      = 'rental_system/rental/work_hours';
    const XML_PATH_HOLIDAYS        = 'rental_system/rental/holidays';
    const XML_PATH_POLICY_TEXT     = 'rental_system/policy/policy';
    const XML_PATH_POLICY_REQUIRED = 'rental_system/policy/required';

    /** @var string */
    protected $scope;

    /** @var string */
    protected $scopeSave;

    /** @var int */
    protected $scopeId;

    /** @var Context */
    protected $_context;

    /** @var ScopeConfigInterface */
    protected $_scopeConfig;

    /** @var Config */
    protected $_configResource;

    /** @var ManagerInterface */
    protected $_message;

    /** @var Json */
    protected $json;

    /**
     * Validate constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $configResource
     * @param ManagerInterface $message
     * @param Context $context
     * @param Json $json
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Config $configResource,
        ManagerInterface $message,
        Context $context,
        Json $json
    ) {
        $this->_scopeConfig    = $scopeConfig;
        $this->_configResource = $configResource;
        $this->_message        = $message;
        $this->_context        = $context;
        $this->json            = $json;
        $this->scope           = 'default';
        $this->scopeSave       = 'default';
        $this->scopeId         = 0;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            //check scope
            if ($storeId = $this->_context->getRequest()->getParam('store')) {
                $this->scope     = ScopeInterface::SCOPE_STORE;
                $this->scopeSave = ScopeInterface::SCOPE_STORES;
                $this->scopeId   = $storeId;
            } elseif ($websiteId = $this->_context->getRequest()->getParam('website')) {
                $this->scope     = ScopeInterface::SCOPE_WEBSITE;
                $this->scopeSave = ScopeInterface::SCOPE_WEBSITES;
                $this->scopeId   = $websiteId;
            }

            //validate work hours
            $workHours = $this->getConfig(self::XML_PATH_WORK_HOURS);
            $hours     = explode(',', $workHours);
            if ($hours[1] <= $hours[0]) {
                $hours[1]  = 23;
                $workHours = implode(',', $hours);
                $this->_message->addWarningMessage(
                    __('Start hour must be earlier than End hour. End hour has been set to 11 p.m.')
                );
                $this->saveConfig(self::XML_PATH_WORK_HOURS, $workHours);
            }

            //validate holiday
            $holidays  = $this->getConfig(self::XML_PATH_HOLIDAYS);
            $wrongDate = false;
            if ($holidays) {
                $holidays = $this->json->unserialize($holidays);
                foreach ($holidays as $key => $date) {
                    if (isset($date['date'])) {
                        if (!preg_match('/^[0-9]{4}[\/-][0-9]{2}[\/-][0-9]{2}\z/', $date['date'])) {
                            $wrongDate = true;
                            unset($holidays[$key]);
                        }
                    }
                }

                if ($wrongDate) {
                    $this->_message->addWarningMessage(
                        __('One or more holidays did not follow yyyy/mm/dd input format. These dates are removed.')
                    );
                    $this->saveConfig(self::XML_PATH_HOLIDAYS, $this->json->serialize($holidays));
                }
            }

            //validate policy
            $policy = $this->getConfig(self::XML_PATH_POLICY_TEXT);
            if (empty($policy) && $this->_scopeConfig->isSetFlag(self::XML_PATH_POLICY_REQUIRED)) {
                $this->_message->addWarningMessage(
                    __('Policy must not be blank if confirmation is required. Please set your rental policy!')
                );
                $this->saveConfig(self::XML_PATH_POLICY_REQUIRED, 0);
            }
        } catch (\Exception $e) {
            $this->_message->addErrorMessage($e);
        }
    }

    /**
     * @param $path
     * @return mixed
     */
    protected function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, $this->scope, $this->scopeId);
    }

    /**
     * @param $path
     * @param $value
     * @return Config
     */
    protected function saveConfig($path, $value)
    {
        return $this->_configResource->saveConfig($path, $value, $this->scopeSave, $this->scopeId);
    }
}
