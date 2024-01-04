<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace AscentDigital\NetsuiteConnector\Block\Information;
use AscentDigital\NetsuiteConnector\Model\ResourceModel\CustomerFileUpload\Collection;
class Information extends \Magento\Framework\View\Element\Template
{

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Collection $customerFileUploadFactory,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->customerFileUploadFactory = $customerFileUploadFactory;
        parent::__construct($context, $data);
    }
    public function _prepareLayout()
    {
        $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();

        if ($breadcrumbsBlock) {

            $breadcrumbsBlock->addCrumb(
                'Home',
                [
                'label' => __('Home'), //lable on breadCrumbes
                'title' => __('Home'),
                'link' => $baseUrl
                ]
            );
            $breadcrumbsBlock->addCrumb(
                'Contact Us',
                [
                'label' => __('Contact Us'),
                'title' => __('Contact Us'),
                'link' => '' //set link path
                ]
            );
        }
        // $this->pageConfig->getTitle()->set(__('FAQ')); // set page name
        return parent::_prepareLayout();
    }
    public function getSessionCustomer()
    {
        if ($this->customerSession->isLoggedIn()) {
            return $this->customerSession->getCustomer(); // get customer attribute
        }
        return false;
    }
    public function getCustomerInfo()
    {
       $customer = $this->getSessionCustomer();
       if($customer){
        return $customer->getCustomerInfo();
       }
       return false;
    }
    public function getCustomerId()
    {
       $customer = $this->getSessionCustomer();
       if($customer){
        return $customer->getId();
       }
       return false;
    }
    public function getCustomerFiles(){
        $collection = $this->customerFileUploadFactory;
        $collection->addFieldToFilter('customer_id',$this->getCustomerId());
        // print_r($collection->getData());die('mateeen');
        return $collection;
     }

}