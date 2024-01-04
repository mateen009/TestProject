<?php
// CHM-MA



/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace AscentDigital\NetsuiteConnector\Cron\Item;

use Magento\Framework\Filesystem\DirectoryList as Directory;
use Magento\Catalog\Model\Product;

/**
 * DeletedProduct Controller
 *
 * get deleted products from Netsuite
 * and disable deleted product in magento
 */
class DeletedProduct 
{
    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $directory;


    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * Initialize DeletedProduct controller
     *
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        
        Directory $directory,
        Product $product
    ) {
        $this->directory = $directory;
        $this->product = $product;
        
    }

    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/crontest.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('DeletedProduct cron is running');
        // die('cron');
        $root = $this->directory->getRoot();
        require_once($root . '/lib/PHPToolkit/NetSuiteService.php');
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/netsuite_cron.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info("Deleted Products cron is executed.");
        // root directory path
        // die("error");
    //sending email to the customer
        $to = "yasirpayee02@gmail.com";
        $subject = "Deleted Products cron";
        
        $message = "Deleted Products cron run Successfully";
        
        
        $email = mail ($to,$subject,$message);

        $service = new \NetSuiteService();

        $service->setSearchPreferences(false, 20);
        $service->setPreferences(false, true);


        // my custom

        $date = date("Y-m-d"); //date
        $time = date("H:i:s"); //time        
        $current_date = $date . 'T' . $time; // current date with time
        $before_24hrs = date("Y-m-d", strtotime($date . '- 10 months')) . 'T' . $time; //date and time before 24hrs

        // search condition
        // date
        $searchField = new \SearchDateField();
        $searchField->operator = "within";
        $searchField->searchValue = $before_24hrs;
        $searchField->searchValue2 = $current_date;

        // record type
        $type = new \SearchEnumMultiSelectField();
        $type->operator = "anyOf";
        $type->searchValue = ['assemblyItem', 'inventoryItem', 'kitItem', 'lotNumberedInventoryItem', 'lotNumberedAssemblyItem', 'serializedAssemblyItem', 'serializedInventoryItem'];

        $deleteFilter = new \GetDeletedFilter();
        $deleteFilter->deletedDate = $searchField;
        $deleteFilter->type = $type;
        // SearchStringFieldOperator
        // $deleteFilter->scriptId = "SearchStringField";

        $deletedRequest = new \GetDeletedRequest();
        $deletedRequest->getDeletedFilter = $deleteFilter;
        $deletedRequest->pageIndex = 1;


        $response = $service->getDeleted($deletedRequest);
        $totalRecords = $response->getDeletedResult->totalRecords;
        if ($totalRecords > 0) {
            $records = $response->getDeletedResult->deletedRecordList->deletedRecord;
            foreach ($records as $record) {
                $product = $this->product->loadByAttribute('item_ns_internal_id', $record->record->internalId);
                if ($prod) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $productRepository = $objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');
                $prod = $productRepository->get(
                    $$prod->getSku(),
                    true/* edit mode */,
                    0/* global store*/,
                    true/* force reload*/
                );
                    $prod->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                    $prod->save();
                }
                
            }
        }
        $logger->info("Deleted Products cron is finished.");
    }
}
