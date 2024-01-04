<?php
namespace AscentDigital\Reports\Model;

class SalesReps extends \Magento\Framework\Model\AbstractModel
{

    public function isFirstNet()
    {
        $currentWebsiteId = $this->storeManager()->getStore()->getWebsiteId();
        if ($currentWebsiteId == '3') {
            return true;
        }
        return false;
    }
    
    public function getManagerReps($customerId) {
        
           $customerFactory = $this->objectManager()->get('\Magento\Customer\Model\CustomerFactory');
           $customerSession = $this->objectManager()->create('Magento\Customer\Model\Session');
           $firstnet = $this->isFirstNet();
           if(!$firstnet){
               return $customerId;
           }
           //get all reps data
           $customers = array();
           if ($customerSession->getCustomerType() == 1) {
               return $customerId;
           } else if ($customerSession->getCustomerType() == 3) {
               //get sales manager reps
               $customerData = $customerFactory->create()->getCollection()->addAttributeToSelect("entity_id")
                   ->addAttributeToFilter("SalesManager_ID", $customerId)->load();
               foreach ($customerData->getData() as $data) {
                   $customers[] = $data['entity_id'];
               }
               return $customers;
           } else if ($customerSession->getCustomerType() == 4) {
               // get tertory manager reps
               $customerData = $customerFactory->create()->getCollection()->addAttributeToSelect("entity_id")
                   ->addAttributeToFilter("TerritoryManager_ID", $customerId)->load();
               foreach ($customerData->getData() as $data) {
                   $customers[] = $data['entity_id'];
               }
               return $customers;
           } else if ($customerSession->getCustomerType() == 5) {
               // get executive manager reps
               $customerData = $customerFactory->create()->getCollection()->addAttributeToSelect("entity_id")
                   ->addAttributeToFilter("Executive_ID", $customerId)->load();
               foreach ($customerData->getData() as $data) {
                   $customers[] = $data['entity_id'];
               }
               return $customers;
           }
       }
    
    public function objectManager(){
       $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager;
    }
    
    
    public function storeManager(){
        
        $storeManager = $this->objectManager()->get(\Magento\Store\Model\StoreManagerInterface::class);
        return $storeManager;
    }
}
