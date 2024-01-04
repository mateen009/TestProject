<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Custom\AdvanceExchange\Block\Manage;

use Magento\Framework\App\ResourceConnection;
use Magento\Customer\Api\CustomerRepositoryInterface;

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
    protected $request;

    protected $customFactory;
    protected $customdataCollection;
    protected $customer;

    protected $customerRepository;
    /**
     * @param \Magento\Framework\App\Request\Http $request
     */

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        ResourceConnection $resource,
        \Magento\Customer\Model\Session $customer,
        \Custom\AdvanceExchange\Model\AdvancedExchange $advanceexchangeFactory,
        \Custom\AdvanceExchange\Model\ResourceModel\AdvancedExchange\CollectionFactory $customdataCollection,
        \Magento\Framework\App\Request\Http $request,
        CustomerRepositoryInterface $customerRepository,
        array $data = []
    ) {
        $this->resource = $resource;
        $this->customer = $customer;
        $this->advanceexchangeFactory = $advanceexchangeFactory;
        $this->customdataCollection = $customdataCollection;
        $this->customerRepository = $customerRepository;
        $this->request = $request;

        parent::__construct($context, $data);
    }
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('My Custom Pagination'));
        parent::_prepareLayout();
        $page_size = $this->getPagerCount();
        $page_data = $this->getAllRecords();
        if ($this->getAllRecords()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'custom.pager.name'
            )
                ->setAvailableLimit($page_size)
                ->setShowPerPage(true)
                ->setCollection($page_data);
            $this->setChild('pager', $pager);
            $this->getAllRecords();
        }
        return $this;
    }
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

   

    public function getPagerCount()
    {
        // get collection
        $minimum_show = 10; // set minimum records
        $page_array = [];
        $list_data = $this->customdataCollection->create();
        $list_count = ceil(count($list_data->getData()));
        $show_count = $minimum_show + 1;
        if (count($list_data->getData()) >= $show_count) {
            $list_count = $list_count / $minimum_show;
            $page_nu = $total = $minimum_show;
            $page_array[$minimum_show] = $minimum_show;
            for ($x = 0; $x <= $list_count; $x++) {
                $total = $total + $page_nu;
                $page_array[$total] = $total;
            }
        } else {
            $page_array[$minimum_show] = $minimum_show;
            $minimum_show = $minimum_show + $minimum_show;
            $page_array[$minimum_show] = $minimum_show;
        }
        return $page_array;
    }

    public function getAllRecords()
    {
        //getting customer internal id
        $internalId = $this->getInternalId();
        //starting page from 1
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        //show 10 records in one page
        $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 10;
        //getting parameters
        $paramsData = $this->request->getParam('exchangetype');
        $paramCaseStatus=$this->request->getParam('case_status');
        $paramApprovalStatus=$this->request->getParam('approval_status');
        //getting collection
        $results = $this->getRecordCollection();
        //apply filter of internal id
        $results->addFieldToFilter('internalid', $internalId);
        //condition for exchange type filter
        if (isset($paramsData) && $paramsData != 'all') {
            if ($paramsData == 'request') {
                $paramsData = 'Ship on Request';
            } else if ($paramsData == 'repair') {
                $paramsData = 'Repair and Return';
            } else if ($paramsData == 'return') {
                $paramsData = 'Ship On Return';
            } else if ($paramsData == 'lost') {
                $paramsData = 'Lost or Stolen';
            }
            //apply filter of exchange type
            $results->addFieldToFilter('exchangetype', $paramsData);
        }
        //condition for case status filter
        if(isset($paramCaseStatus) && $paramCaseStatus !='all'){
            if($paramCaseStatus =='Closed'){
                $paramCaseStatus ='Closed';
            }  else if($paramCaseStatus == 'Completed'){
                $paramCaseStatus ='Completed';
            }   else if($paramCaseStatus=='Cancelled'){
                $paramCaseStatus ='Cancelled';
            }
            //apply filter of case status
            $results->addFieldToFilter('case_status', $paramCaseStatus);
        }
        //condition for approval status filter
        if(isset($paramApprovalStatus) && $paramApprovalStatus !='all'){
            if($paramApprovalStatus =='pending'){
                $paramApprovalStatus ='Pending Approval';
            }else if($paramApprovalStatus == 'Approved'){
                $paramApprovalStatus ='Approved';
            }
            //apply filter of approval status
            $results->addFieldToFilter('approval_status', $paramApprovalStatus);

        }
        //setting page size
            $results->setPageSize($pageSize);
        //set current page
            $results->setCurPage($page);
            return $results;
    }

public function getRecordCollection(){
    $result = $this->advanceexchangeFactory->getCollection()
    ->setOrder('casenumber','DESC');
    return $result;
}


    public function getSkus()
    {
        $customer = $this->customer;
        $customerId = 0;
        if ($customer->isLoggedIn()) {
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
        if ($customer->isLoggedIn()) {
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