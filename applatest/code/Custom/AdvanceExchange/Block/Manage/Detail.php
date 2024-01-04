<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Custom\AdvanceExchange\Block\Manage;

use Magento\Framework\App\ResourceConnection;
use Magento\Customer\Api\CustomerRepositoryInterface;

class Detail extends \Magento\Framework\View\Element\Template
{

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param array $data
     */

     /**
     * @var ResourceConnection
     */
    private $resource;


    protected $customer;

    protected $customerRepository;
   

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        ResourceConnection $resource,
        \Magento\Customer\Model\Session $customer,
        CustomerRepositoryInterface $customerRepository,
        array $data = []
    ) {
        $this->resource = $resource;
        $this->customer = $customer;
        $this->customerRepository = $customerRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context, $data);
    }

    public function getAEDetail($id)
    {
      $connection = $this->resource->getConnection();
      $table = $connection->getTableName('custom_advanceexchange_advanced_exchange');
      $query = "SELECT * FROM `{$table}` WHERE `advanced_exchange_id` = {$id}";
      $result = $connection->fetchAll($query);
      if($result) {
        return $result[0];
      } else {
        return '';
      }
    }
    public function getOrderCollectionBySONumber($SONumber)
    {
        $id = $this->orderCollectionFactory->create()
         ->addFieldToFilter('ns_so_number', $SONumber)
         ->getFirstItem()->getId(); //Add condition if you wish
        //  echo "<pre>";print_r($collection);die('mateen');
      
      return $id;
      
     }

    public function getAllRecords()
    {
     // $link = $this->_storeManager->getStore()->getUrl("advanceexchange/manage");
     
      //
   //   if(isset($paramsData)){
      $internalid = $this->getInternalId();
      $connection = $this->resource->getConnection();
      $table = $connection->getTableName('custom_advanceexchange_advanced_exchange');
      $query = "SELECT * FROM `{$table}` WHERE `internalid` = {$internalid}";
      $result = $connection->fetchAll($query);
      return $result;
      
    }

    public function getSkus()
    {
        $customer = $this->customer;
        $customerId = 0;
        if($customer->isLoggedIn()) {
            $customerId = $customer->getId();
        }
        //$customerId = 111;
        $skus = array();

        $connection = $this->resource->getConnection();

        $table = $connection->getTableName('eligible_sku');
        
       // $query = "Select sku FROM {$table}  WHERE customer_id = {$customerId}";
        $query = "SELECT `main_table`.entity_id, `order_item`.sku FROM `sales_order` AS `main_table` 
                  INNER JOIN `sales_order_item` AS `order_item` ON main_table.entity_id = order_item.order_id
                  INNER JOIN `eligible_sku` AS `es` ON order_item.sku = es.sku 
                  WHERE `main_table`.`customer_id` = {$customerId} GROUP BY `order_item`.sku";
        $result = $connection->fetchAll($query);
        return $result;
        // if(count($result) > 0) {
        //     foreach($result as $data) {
        //         echo "<pre>";print_r($data);echo "</pre>";
        //     }
        // }

    }

    public function getCustomerId()
    {
        $customer = $this->customer;
        $customerId = 0;
        if($customer->isLoggedIn()) {
            $customerId = $customer->getId();
        }
        return $customerId;
    }

    public function getInternalId()
    {
        $customerId = $this->getCustomerId();
        $customerData = $this->customerRepository->getById($customerId);
        return $customerData->getCustomAttribute('ns_internal_id')->getValue();
    }
}

