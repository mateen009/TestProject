<?php

namespace Mobility\SalesTeamManagement\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;

class ManagerUpdate implements ObserverInterface
{
  protected $_request;
  protected $customer;
  protected $customerFactory;
  protected $_customerRepositoryInterface;

  public function __construct(
    Customer $customer,
    \Magento\Framework\View\Element\Context $context,
    CustomerFactory $customerFactory,
    \Magento\Customer\Api\CustomerRepositoryInterface $customerRepoInterface
  ) {
    $this->customer = $customer;
    $this->_request = $context->getRequest();
    $this->customerFactory = $customerFactory;
    $this->_customerRepositoryInterface = $customerRepoInterface;
  }

  /**
   * @param \Magento\Framework\Event\Observer $observer
   * @return void
   */
  public function execute(EventObserver $observer)
  {
    $postData = $this->_request->getPostValue();
    $postdata = $postData['customer'];
    if (isset($postData['customer_id'])) {
      $custData = $this->customer->load($postData['customer_id']);

      $customerType = $postdata['Customer_Type'];

      if ($customerType == 3 || $customerType == 4) {

        $currentCTId = $postData['customer_id'];
        $oldSmId = $custData->getData('SalesManager_ID');
        $newSmId = $postdata['SalesManager_ID'];

        $oldTmId = $custData->getData('TerritoryManager_ID');
        $newTmId = $postdata['TerritoryManager_ID'];

        $oldEmId = $custData->getData('Executive_ID');
        $newEmId = $postdata['Executive_ID'];

        if ($customerType == 3) { //Sales Manager
          $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/customerData.log');
          $logger = new \Zend_Log();
          $logger->addWriter($writer);
          $logger->info('SM ID: ' . $currentCTId);

          $_customers = $this->getCustomerCollection($currentCTId);
          // echo $_customers->count();die();
          foreach ($_customers as $customer) {
            // echo "<pre>";print_r($customer->getData());die();
            $_cId = $customer->getData('entity_id');
            $logger->info('Updated SR ID: ' . $_cId);

            $smcData = $this->_customerRepositoryInterface->getById($_cId);
            if ($oldTmId !== $newTmId || $oldEmId !== $newEmId) {
              if ($oldTmId !== $newTmId) {
                $newTmId = intval( $newTmId );
                $logger->info('NEW TM ID: ' . $newTmId);
                $smcData->setData('TerritoryManager_ID', $newTmId);
                $smcData->setCustomAttribute('TerritoryManager_ID', $newTmId);
              }
              if ($oldEmId !== $newEmId) {
                $newEmId = intval( $newEmId );
                $logger->info('NEW ET ID: ' . $newEmId);
                $smcData->setData('Executive_ID', $newEmId);
                $smcData->setCustomAttribute('Executive_ID', $newEmId);
              }
              $this->_customerRepositoryInterface->save($smcData);
            }
            // echo "<pre>";print_r($smcData->getData());die();

          }
          $logger->info('----------------------------------------------');
        }

        if ($customerType == 4) { //Territory  Manager
          $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/customerData.log');
          $logger = new \Zend_Log();
          $logger->addWriter($writer);
          $logger->info('TM ID: ' . $currentCTId);

          $_customers = $this->getTMCustomerCollection($currentCTId);
          // echo $_customers->count();die();
          foreach ($_customers as $customer) {
            $_cId = $customer->getData('entity_id');
            //if($_cId == 156) {
               //echo "<pre>";print_r($customer->getData());die();
            $logger->info('Updated SR/SM ID: ' . $_cId);

           // $smcData = $this->customer->load($_cId);
            $smcData = $this->_customerRepositoryInterface->getById($_cId);
            //echo "<pre>";print_r($smcData->getData());die();

            if ($oldEmId !== $newEmId) {
              $newEmId = intval( $newEmId );
              $logger->info('NEW ET ID: ' . $newEmId);
              $smcData->setData('Executive_ID', $newEmId);
              $smcData->setCustomAttribute('Executive_ID', $newEmId);
            }
            $this->_customerRepositoryInterface->save($smcData);

            // echo "<pre>";print_r($smcData->getData());die();
          //}

          }
          $logger->info('----------------------------------------------');
        }
      }
      //echo $oldData." : ".$newData;die(" ::");
    }
  }

  public function getCustomerCollection($smId)
  {
    return $this->customerFactory->create()->getCollection()
      ->addAttributeToSelect('*')
      ->addAttributeToFilter("SalesManager_ID", $smId)
      ->load();
  }

  public function getTMCustomerCollection($tmId)
  {
    return $this->customerFactory->create()->getCollection()
      ->addAttributeToSelect('*')
      ->addAttributeToFilter("TerritoryManager_ID", $tmId)
      ->load();
  }
}
