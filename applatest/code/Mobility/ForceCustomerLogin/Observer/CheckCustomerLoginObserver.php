<?php

namespace Mobility\ForceCustomerLogin\Observer;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Mobility\ForceCustomerLogin\Cookie\Customcookie;

/**
 * Class CheckCustomerLoginObserver
 * @package Mobility\ForceCustomerLogin\Observer
 */
class CheckCustomerLoginObserver implements ObserverInterface
{
    const EXCLUDE_ACTIONS = [
        'contact_index_index',
        'loginascustomer_login_index',
        'customer_account_create',
        'customer_account_createpost',
        'customer_account_login',
        'customer_account_loginpost',
        'customer_account_logout',
        'customer_account_confirm',
        'customer_account_forgotpassword',
        'customer_account_forgotpasswordpost',
        'customer_account_resetpassword',
        'customer_account_resetpasswordpost',
        'customer_account_createPassword',
        'customer_account_logoutsuccess'
    ];

    const OPEN_ACTIONS = array(
        'loginpost',
        'logoutsuccess',
        'createPassword',
        'forgotpassword',
        'forgotpasswordpost',
        'resetpassword',
        'resetpasswordpost'
    );

    const EXCLUDE_PAGES = array(
        '/contact',
        '/contact/',
        '/faq',
        '/orderapproval/order/emailapproval',
        '/orderapproval/order/emailapproval/',
        '/customer/account/createPassword/',
        '/customer/account/createPassword',
        '/createPassword/',
        '/createPassword',
        '/customer/account/createpassword/',
        '/customer/account/createpassword',
        '/createpassword/',
        '/createpassword',
        '/faq/',
        '/nsconnector/product/customerspecificpricing/',
        '/nsconnector/product/customerspecificpricing'
    );

    const REDIRECT_AFTER_LOGIN = array(
        '/firstnet/quote/account/orderapprovals/',
        '/salesforce/index/addquote'
    );

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
    protected $request;
    protected $_checkoutSession;

    /**
     * CheckCustomerLoginObserver constructor.
     * @param Session $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */

    public function __construct(
        CustomerSession $customerSession,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Checkout\Model\Session $checkoutSession,
        \PerksAtWork\NextJumpSFTP\Model\ResourceModel\CollectionFactory $collectionFactory,
        Customcookie $customcookie
    ) {
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->collectionFactory = $collectionFactory;
        $this->customcookie = $customcookie;
        $this->_checkoutSession = $checkoutSession;
        $this->request = $request;
    }

    /**
     * @param Observer $observer
     * @return $this
     * @throws LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $csid = $this->request->getParam('csid');

        $customURI = $_SERVER['REQUEST_URI'];
        if (!$this->customerSession->isLoggedIn() && str_contains($customURI, "?mtel_id")) {
            $this->customcookie->set($customURI, 3600);
        }

        if ($this->customerSession->getCustomerGroupId()) {

            $customerStoreId = $this->customerSession->getCustomer()->getStoreId();
            $storeId = $this->storeManager->getStore()->getId();
            if ($storeId == $customerStoreId) {
                return $this; //if in allowed actions do nothing.
            }
            $this->storeManager->setCurrentStore($customerStoreId);
            $url = $this->storeManager->getStore()->getBaseUrl();
            $observer->getControllerAction()->getResponse()->setRedirect($url);
        } else {
            $actionName = $observer->getEvent()->getRequest()->getFullActionName();
            $controller = $observer->getControllerAction();
            // Allow excluded actions
            if (in_array($actionName, self::EXCLUDE_ACTIONS)) {
                // $bar = 1;
                return $this;
            }
            // Allow excluded pages
            if (in_array($observer->getEvent()->getRequest()->getOriginalPathInfo(), self::EXCLUDE_PAGES)) {
                // $bar = 1;
                return $this;
            }

            if ($controller == 'account' && in_array($actionName, self::OPEN_ACTIONS)) {
                // $bar = 1;
                return $this; //if in allowed actions do nothing.
            }
            $storeId = $this->storeManager->getStore()->getId();
            if ($storeId == 7 && $actionName != 'customer_account_loginPost') {
                //csid dummy array for condition to check if csid exist in this list then proceed
                // $csidArray = array("123", "456", "789");
                $collection = $this->collectionFactory->create()
                    ->addFieldToFilter('csid', $csid)->getFirstItem();
                // echo $_SERVER['HTTP_REFERER'];
                // $refererUrl = $this->request->getServer('HTTP_REFERER');
                $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
                // $referer = isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER']): null;
                // var_dump($referer);
                // var_dump(get_header());
                // die;
                // die();
                if(1){
                // we have commented this code because client told us to comment this code in 20 nov 2023
                // if ($referer && ($referer == 'https://www.perksatwork.com/' || $referer == 'https://www.perksatwork.com' || $referer == 'https://www.corporateperks.com/' || $referer == 'https://www.corporateperks.com')) {
                    // if ($collection->getData()) {
                    //     if ($csid) {
                    //         $this->getCheckoutSession()->setDuplicateCsid($csid);
                    //         $this->getCheckoutSession()->setCsid(NULL);
                    //     }
                    // } else {
                        if ($csid) {
                            $this->getCheckoutSession()->setCsid($csid);
                            // $this->getCheckoutSession()->setDuplicateCsid(NULL);
                        }
                    // }
                    $this->getCheckoutSession()->setRefererPopup(false);
                } else {
                    $this->getCheckoutSession()->setRefererPopup(true);
                    $this->getCheckoutSession()->setCsid(NULL);
                    // $this->getCheckoutSession()->setDuplicateCsid(NULL);
                }
                return $this;
            }
            if ($actionName == 'paystandmagento_webhook_paystand'){
                return $this;
            }
            $url = $this->storeManager->getStore()->getUrl('customer/account/login');
            $observer->getControllerAction()->getResponse()->setRedirect($url);
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
    public function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }
}
