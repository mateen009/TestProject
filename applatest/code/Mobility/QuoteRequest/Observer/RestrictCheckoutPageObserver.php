<?php

namespace Mobility\QuoteRequest\Observer;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Mobility\QuoteRequest\Api\QuoteRequestRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class RestrictCheckoutPageObserver
 * @package Mobility\QuoteRequest\Observer
 */
class RestrictCheckoutPageObserver implements ObserverInterface
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var QuoteRequestRepositoryInterface
     */
    private $quoteRequestRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $addresss;
    

    /**
     * RestrictCheckoutPageObserver constructor.
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param QuoteRequestRepositoryInterface $quoteRequestRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        QuoteRequestRepositoryInterface $quoteRequestRepository,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        \Magento\Customer\Model\AddressFactory $addresss
    ) {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRequestRepository = $quoteRequestRepository;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->addresss = $addresss;
    }

    /**
     * @param Observer $observer
     * @return $this
     * @throws LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->customerSession->getCustomerGroupId()) {
            // echo '<pre>';
            // var_dump($_SESSION);
            // echo '</pre>';
            // return $this;
            $quote = $this->checkoutSession->getQuote();
            $quoteRequest = $this->getCustomerQuoteRequestList();
            if ($quoteRequest->getId() && $quoteRequest->getSelectAddress() == 'same') {
                $customer = $this->customerSession->getCustomer();
                $shippingAddressId = $customer->getDefaultShipping();
                $billingAddressId = $customer->getDefaultBilling();
                if ($shippingAddressId && empty($quoteRequest->getPreviousDefaultShipping())) {
                    $quoteRequest->setPreviousDefaultShipping($shippingAddressId);
                    $quoteRequest->save();
                }
                if ($billingAddressId && empty($quoteRequest->getPreviousDefaultBilling())) {
                    $quoteRequest->setPreviousDefaultBilling($billingAddressId);
                    $quoteRequest->save();
                }
                $firstName = "";
                $lastName = "";
                $name = explode(" ", $quoteRequest->getCustomerName());
                $count =  str_word_count($quoteRequest->getCustomerName());

                if ($count == 1) {
                    $firstName = $name['0'];
                    $lastName =  $name['0'];
                }

                if ($count == 2) {
                    $firstName = $name['0'];
                    $lastName =  $name['1'];
                }
                if ($count > 2) {
                    $firstName = $name['0'];
                    $lastName =  $name['1'] . " " . $name['2'];
                }
                $address = $this->addresss->create();

                $address->setCustomerId($quote->getCustomerId())
                    ->setFirstname($firstName)
                    ->setLastname($lastName)
                    ->setCountryId('US')
                    ->setPostcode($quoteRequest->getAgencyZipcode())
                    ->setCity($quoteRequest->getAgencyCity())
                    ->setTelephone($quoteRequest->getcustomerPhone())
                    ->setRegion($quoteRequest->getAgencyState())
                    ->setCompany('')
                    ->setStreet($quoteRequest->getAgencyStreet())
                    ->setIsDefaultBilling(1)
                    ->setIsDefaultShipping(1)
                    ->setSaveInAddressBook('0');
                try {
                    $address->save();
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(
                        __($e->getMessage())
                    );
                    return $this->resultRedirectFactory->create()->setPath('quote/index');
                }
                return $this;
            } else {
                return $this;
            }
            // $url = $this->storeManager->getStore()->getUrl('quote');
            // $observer->getControllerAction()->getResponse()->setRedirect($url);
        } else {
            
             $storeId = $this->storeManager->getStore()->getId();
             $csid ='';
            //  if(isset($_SESSION["csid"])){
                $getCheckoutCsid = $this->checkoutSession->getCsid();
             if(isset($getCheckoutCsid)){
            // $csid =  $_SESSION["csid"];
            $csid =  $getCheckoutCsid;
             }
            // $csid =  $this->checkoutSession->getCsid();
            $duplicateCsid ='';
            // if(isset($_SESSION["duplicatecsid"])){
                $getCheckoutDuplicateCsid = $this->checkoutSession->getDuplicateCsid();
            if(isset($getCheckoutDuplicateCsid)){
            // $duplicateCsid =  $_SESSION["duplicatecsid"];
            $duplicateCsid =  $getCheckoutDuplicateCsid;
            }
            // $duplicateCsid =  $this->checkoutSession->getDuplicateCsid();
            // print_r($_SESSION["favcolor"]);
            // print_r($csid);die;
            if($storeId ==7){
                if($csid){
                    return $this;
        
                }
                // we have commented this code because client told us to comment this code in 20 nov 2023 meeting.
                // elseif ($duplicateCsid){
                //     $url = $this->storeManager->getStore()->getUrl('checkout/cart/');
                //     $observer->getControllerAction()->getResponse()->setRedirect($url);
                // }
                else{
                    $url = $this->storeManager->getStore()->getUrl('checkout/cart/');
                    $observer->getControllerAction()->getResponse()->setRedirect($url);
                }
            }
            else{
            $url = $this->storeManager->getStore()->getUrl('customer/account/login');
            $observer->getControllerAction()->getResponse()->setRedirect($url);
            }
        }
    }

    /**
     * Fetch Customer Quote Request List
     *
     * @return mixed
     */
    public function getCustomerQuoteRequestList()
    {
        $quote = $this->checkoutSession->getQuote();
        $requestQuoteCollection = $this->quoteRequestRepository->getCustomerQuoteRequestList($quote->getCustomerId(), ['approved'], $quote->getId());
        return $requestQuoteCollection->getFirstItem();
    }
}
