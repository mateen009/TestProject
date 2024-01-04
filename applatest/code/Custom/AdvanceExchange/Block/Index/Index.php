<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Custom\AdvanceExchange\Block\Index;

use Magento\Framework\App\ResourceConnection;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Directory\Model\CountryFactory;

class Index extends \Magento\Framework\View\Element\Template
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

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var CountryFactory
     */
    private $countryFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        ResourceConnection $resource,
        \Magento\Customer\Model\Session $customer,
        CustomerRepositoryInterface $customerRepository,
        AddressRepositoryInterface $addressRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CountryFactory $countryFactory,
        array $data = []
    ) {
        $this->resource = $resource;
        $this->customer = $customer;
        $this->customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;
        $this->countryFactory = $countryFactory;
        $this->storeManager = $storeManager;
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
                'Depot Services',
                [
                'label' => __('Depot Services'),
                'title' => __('Depot Services'),
                'link' => '' //set link path
                ]
            );
        }
        // $this->pageConfig->getTitle()->set(__('FAQ')); // set page name
        return parent::_prepareLayout();
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
        $customerId = '';
        if($customer->isLoggedIn()) {
            $customerId = $customer->getId();
        }
        return $customerId;
    }

    public function getInternalId()
    {
        $customerId = $this->getCustomerId();
        $customerData = $this->getCustomer($customerId);
        return $customerData->getCustomAttribute('ns_internal_id')->getValue();
    }

    public function getCustomer($cid){
        return $this->customerRepository->getById($cid);
    }

    public function getShippingInfo($customerId)
    {
        $customer = $this->customerRepository->getById($customerId);
        $shippingAddressId = $customer->getDefaultShipping();

        $shippingData = array();
        $streetAddress1 = '';
        $streetAddress2 = '';
        $shippingName = '';
        $shippingCity = '';
        $shippingRegion = '';
        $shippingZip = '';

        if($shippingAddressId) {
            $shippingAddress = $this->addressRepository->getById($shippingAddressId);

            $shippingRegion = $shippingAddress->getRegion()->getRegion(); // Get region
            $shippingCity = $shippingAddress->getCity(); // Get city
            $shippingZip =  $shippingAddress->getPostcode(); // Get postcode

            $shippingName = $shippingAddress->getFirstname()." ".$shippingAddress->getLastname();
            $streetAddress = $shippingAddress->getStreet();
            if(isset($streetAddress[0])) {
                $streetAddress1 = $streetAddress[0];
            }
            if(isset($streetAddress[1])) {
                $streetAddress2 = $streetAddress[1];
            }
        }
        //Get country name
        //print_r($streetAddress);die();
        // $countryCode = $shippingAddress->getCountryId();
        // $country = $this->countryFactory->create()->loadByCode($countryCode);
        // echo $country->getName();
       // die();

       $shippingData['shippingLabel'] = $shippingName;
       $shippingData['shippingStreet1'] = $streetAddress1;
       $shippingData['shippingStreet2'] = $streetAddress2;
       $shippingData['shippingCity'] = $shippingCity;
       $shippingData['shippingState'] = $shippingRegion;
       $shippingData['shippingZip'] = $shippingZip;

       //echo "<pre>";print_r($shippingData);die();
       return $shippingData;
    }
}

