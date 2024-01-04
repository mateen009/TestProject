<?php

namespace Mobility\ForceCustomerLogin\Plugin;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Mobility\ForceCustomerLogin\Cookie\Customcookie;

class LoginPost
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Customer session
     *
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $customcookie;

    /**
     * CustomerLogin constructor.
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerSession $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerSession $customerSession,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Customcookie $customcookie
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;;
        $this->storeManager = $storeManager;
        $this->customcookie = $customcookie;
    }

    public function afterExecute(
        \Magento\Customer\Controller\Account\LoginPost $subject,
        $result
    ) {
        $customerId = $this->customerSession->getCustomer()->getId();
        // check if customer id exist by Mateen
        if ($customerId) {
            $storeId = $this->storeManager->getStore()->getStoreId();
            // store id: 1, Mobility Store
            $url = $this->storeManager->getStore()->getBaseUrl();
            if ($storeId == 1) {
                $result->setPath($url . $this->getTargetUrl());
                return $result;
            } elseif ($storeId == 2) {
                $customer = $this->customerRepository->getById($customerId);
                $customerStoreId = $customer->getStoreId();
                $customerType = $customer->getCustomAttribute('Customer_Type');
                if ($customerType) {
                    $this->customerSession->setCustomerType($customerType->getValue());
                }
                if ($customerStoreId) {
                    $this->storeManager->setCurrentStore($customerStoreId);
                }

                //redirect back to add quote referral url 
                $_baseUrl = $this->scopeConfig->getValue("web/unsecure/base_url");
                $customURI = $this->customcookie->get();
                if (isset($customURI)) {
                    $newUrl = $_baseUrl . $customURI;
                    $url = $newUrl;
                    $this->customcookie->set('');
                }
                if ($customerType) {
                    if ($customerType->getValue() == 3 || $customerType->getValue() == 4 || $customerType->getValue() == 5) {
                        $result->setPath('customreports/firstnetreports/dashboard/');
                        return $result;
                    }
                }
            } elseif ($storeId == 5) {
                // $result->setPath('order-new-product-ariba');
                $result->setPath('/');
                return $result;
            }
        }
    }

    /**
     * Get Target Url
     * 
     * @return string
     */
    public function getTargetUrl()
    {
        $result = $this->scopeConfig->getValue(
            'customer/redirect/target_url',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getStoreId()
        );

        return $result ?? 'ordering.html';
    }
}
