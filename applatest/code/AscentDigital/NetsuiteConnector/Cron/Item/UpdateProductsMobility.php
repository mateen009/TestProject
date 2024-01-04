<?php
// CHM-MA



/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace AscentDigital\NetsuiteConnector\Cron\Item;

use Magento\Framework\Filesystem\DirectoryList as Directory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product;
use Magento\Framework\Filesystem\Driver\File;
use AscentDigital\NetsuiteConnector\Model\SaveItemDetailsFactory as ItemLocation;
use AscentDigital\NetsuiteConnector\Model\NSCronFactory;
use AscentDigital\NetsuiteConnector\Helper\ProductHelper;

/**
 * DeletedProduct Controller
 *
 * get deleted products from Netsuite
 * and disable deleted product in magento
 */
class UpdateProductsMobility 
{
    protected $directory;
    
    protected $nsCron;
     

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $product;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $_file;

    protected $messageManager;
    protected $itemLocation;
    protected $productHelper;
    protected $emulation;
    public function __construct(
        ProductFactory $product,
        Product $_product,
        Directory $directory,
        ProductHelper $productHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\App\Emulation $emulation,
        ItemLocation $itemLocation,
        File $file,
        NSCronFactory $nsCron
    ) {
        $this->product = $product;
        $this->_product = $_product;
        $this->directory = $directory;
        $this->_file = $file;
        $this->productHelper = $productHelper;
        $this->messageManager = $messageManager;
        $this->emulation = $emulation;

        $this->itemLocation = $itemLocation;
        $this->nsCron = $nsCron;
        
    }

    public function execute()
    {
        // $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/crontest.log');
        // $logger = new \Zend_Log();
        // $logger->addWriter($writer);
        // $logger->info('UpdateProducts cron is running');
        // die('cron');
        // root directory path
        // $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/netsuite_cron.log');
        $storeIds = [1, 2, 5]; // Array of store IDs

// foreach ($storeIds as $storeId) {
        // $this->emulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);
        $this->emulation->startEnvironmentEmulation(1, 'adminhtml');
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/netsuite_cron.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info("Update Products cron is executed.");
        $root = $this->directory->getRoot();
        require_once($root . '/lib/PHPToolkit/NetSuiteService.php');
        // updated products in last 24hrs
        $this->productHelper->getUpdatedProducts($root, $logger);
        $logger->info("Update Products cron is finished");
        $this->emulation->stopEnvironmentEmulation();
// }


        //     $to = "yasirpayee02@gmail.com";
        //  $subject = "Update Products cron";
         
        //  $message = "Update Products cron run Successfully.";
         
         
        //  $email = mail ($to,$subject,$message);
         

    }
    
    // public function getUpdatedProducts($root, $logger)
    // {


    //     $service = new \NetSuiteService();

    //     $service->setSearchPreferences(false, 50);
    //     // die('hereeee');
    //     $cron = $this->nsCron->create()->load('update_product_cron', 'title');
    //     if ($cron->getData()) {
    //         if ($cron->getIndex() > 1) {
    //             $SearchMoreWithIdRequest = new \SearchMoreWithIdRequest();
    //             // assigning search id 
    //             $SearchMoreWithIdRequest->searchId = $cron->getSearchId();

    //             //assigning next page index
    //             $SearchMoreWithIdRequest->pageIndex = $cron->getIndex();

    //             // search next page result on the basis of search id
    //             $searchResponse = $service->searchMoreWithId($SearchMoreWithIdRequest);
    //             $searchResult = $searchResponse->searchResult;
    //             $totalPages = $searchResult->totalPages;
    //             $pageIndex = $searchResponse->searchResult->pageIndex;
    //             if ($pageIndex < $totalPages) {
    //                 $cron->setIndex($pageIndex + 1);
    //                 $cron->setTotalPages($totalPages);
    //                 $cron->setTitle('update_product_cron');
    //                 $cron->setSearchId($searchResult->searchId);
    //                 $cron->save();
    //             } else {
    //                 $cron->setIndex(0);
    //                 $cron->setTotalPages('');
    //                 $cron->setTitle('update_product_cron');
    //                 $cron->setSearchId('');
    //                 $cron->save();
    //             }
    //         } else {
    //             $searchResponse = $this->searchItemsInNetsuite($service, $cron);
    //         }
    //     } else {
    //         $searchResponse = $this->searchItemsInNetsuite($service, $cron);
    //     }
    //     $this->processSearchResponse($searchResponse, $logger, $root);
    // }

    // public function searchItemsInNetsuite($service, $cron)
    // {
    //     $date = date("Y-m-d"); //date
    //     $time = date("H:i:s"); //time        
    //     $current_date = $date . 'T' . $time;     // current date with time
    //     $before_24hrs = date("Y-m-d", strtotime($date . '- 11 months')) . 'T' . $time;   //date and time before 24hrs
    //     // search condition
    //     $itemSearchField = new \SearchDateField();
    //     $itemSearchField->operator = "within";
    //     $itemSearchField->searchValue = $before_24hrs;
    //     $itemSearchField->searchValue2 = $current_date;
    //     //custom field "custitem_mcg_item_category_id"
    //     $searchValueArray =[];
    //     $searchValue1 = new \ListOrRecordRef();
    //     $searchValue1->internalId = 1;
    //     $searchValue2 = new \ListOrRecordRef();
    //     $searchValue2->internalId = 2;
    //     // $searchValue2->name = 'FirstNet';
    //     $searchValueArray = [$searchValue1, $searchValue2];

    //     $catValueArray =[];
    //     $catValue1 = new \ListOrRecordRef();
    //     $catValue1->internalId = '1';

    //     $catValue2 = new \ListOrRecordRef();
    //     $catValue2->internalId = '2';

    //     $catValue3 = new \ListOrRecordRef();
    //     $catValue3->internalId = '3';

    //     $catValue4 = new \ListOrRecordRef();
    //     $catValue4->internalId = '4';

    //      // magento id 14
    //      $catValue5 = new \ListOrRecordRef();
    //      $catValue5->internalId = '6';
    //     $catValueArray = [$catValue1, $catValue2, $catValue3, $catValue4, $catValue5];

    //     //custom field "custitem_mcg_item_category_id"
    //     $custitem_mcg_item_category_id = new \SearchMultiSelectCustomField();
    //     $custitem_mcg_item_category_id->operator = 'anyOf';
    //     $custitem_mcg_item_category_id->scriptId = 'custitem_mcg_item_category_id';
    //     $custitem_mcg_item_category_id->searchValue = $catValueArray;

    //     // custom field "custitem_catalog_assignment"
    //     $custitem_catalog_assignment = new \SearchMultiSelectCustomField();
    //     $custitem_catalog_assignment->operator = 'anyOf';
    //     $custitem_catalog_assignment->scriptId = 'custitem_catalog_assignment';
    //     $custitem_catalog_assignment->searchValue = $searchValueArray;
    //     $customFieldList = new \SearchCustomFieldList();
    //     $customFieldList->customField = [$custitem_mcg_item_category_id, $custitem_catalog_assignment];
    //     // creating object of ItemSearchBasic class
    //     $itemSearch = new \ItemSearchBasic();
    //     //applying search condition on field
    //     $itemSearch->lastModifiedDate = $itemSearchField;
    //     $itemSearch->customFieldList = $customFieldList;

    //     // creating request
    //     $request = new \SearchRequest();
    //     $request->searchRecord = $itemSearch;

    //     //make soap call of the created request
    //     $searchResponse = $service->search($request);
    //     $searchResult = $searchResponse->searchResult;
    //     $totalPages = $searchResult->totalPages;
    //     $pageIndex = $searchResponse->searchResult->pageIndex;
    //     if ($totalPages > 1) {
    //         if ($cron->getData()) {
    //             $cron->setIndex($pageIndex + 1);
    //             $cron->setTotalPages($totalPages);
    //             $cron->setTitle('update_product_cron');
    //             $cron->setSearchId($searchResult->searchId);
    //             $cron->save();
    //         } else {
    //             $cron = $this->nsCron->create();
    //             $cron->setIndex($pageIndex + 1);
    //             $cron->setTotalPages($totalPages);
    //             $cron->setTitle('update_product_cron');
    //             $cron->setSearchId($searchResult->searchId);
    //             $cron->save();
    //         }
    //     }
    //     return $searchResponse;
    // }

    // public function processSearchResponse($searchResponse, $logger, $root)
    // {
    //     if (!$searchResponse->searchResult->status->isSuccess) {
    //         $messages = $searchResponse->searchResult->status->statusDetail;
    //         foreach ($messages as $message) {
    //             $logger->info($message->message);
    //             //  $this->messageManager->addError(__($message->message));
    //         }
    //     } else {
    //         if ($searchResponse->searchResult->totalRecords > 0) {
    //             $products = $searchResponse->searchResult->recordList->record;
    //             foreach ($products as $product) {
    //                 $productFactory = $this->product->create();
    //                 $prod = $productFactory->loadByAttribute('sku', $product->itemId);
    //                 // update product in magento if exist otherwise create new product
    //                 if ($prod) {
    //                     $this->updateProduct($prod, $product, $root, $logger);
    //                 } else {
    //                     // add product in magento 
    //                     $this->addProduct($product, $root, $logger);
    //                 }
    //             }
    //         } else {
    //             // set index and search id to null
    //         }
    //     }
    // }
    // public function updateProduct($product, $ns_product, $root, $logger)
    // {

    //     //update product quantity
    //     try {
    //         $product->setStockData(
    //             array(
    //                 'use_config_manage_stock' => 0,
    //                 'manage_stock' => 1,
    //                 'is_in_stock' => 1,
    //                 'qty' => 100
    //             )
    //         );
    //         $product->addAttributeUpdate('short_description', $ns_product->salesDescription ? $ns_product->salesDescription : '', '0');
    //         $product->addAttributeUpdate('short_description', $ns_product->salesDescription ? $ns_product->salesDescription : '', '2');
    //         $product->setItemNsInternalId($ns_product->internalId);
    //         // $locations = array();
    //         $locations = array();
    //         foreach ($ns_product->locationsList->locations as $loc) {

    //             try {
    //                 $locations[] = $loc->locationId->internalId;
    //                 $itemLocation = $this->itemLocation->create();
    //                 $itemLocation->setData('item_id', $product->getId());
    //                 $itemLocation->setData('location_name', $loc->location);
    //                 $itemLocation->setData('item_internal_id', $product->getItemNsInternalId());
    //                 $itemLocation->setData('location_id', $loc->locationId->internalId);
    //                 $itemLocation->setData('name', $loc->locationId->name);
    //                 $itemLocation->setData('qty', 10);
    //                 $itemLocation->save();
    //             } catch (\Exception $ex) {
    //             }
    //         }
    //         if ($locations) {
    //             if (in_array('1', $product->getWebsiteIds())) {
    //                 $loc_ids = implode(',', $locations);
    //                 $product->setStoreId(1);
    //                 $product->setLocationId($loc_ids);
    //             }
    //         }
    //         $product->save();
    //         // $logger->info($ns_product->itemId);
    //         // echo "Product with sku: " . $ns_product->itemId . " updated successfully!\n";
    //         $logger->info(__('Product with sku: ' . $ns_product->itemId . ' updated successfully!'));
    //         return;
    //     } catch (\Exception $ex) {
    //         // $logger->info($ns_product->itemId);
    //         $logger->debug(__('AscentDigital\NetsuiteConnector\Cron\Item\UpdateProducts' . $ex->getMessage() . ' with sku: ' . $ns_product->itemId));
    //         return;
    //     }
    // }
    // public function addProduct($pro, $root, $logger)
    // {

    //     //intitializing store ids
    //     $storeIds = array();

    //     //intitializing store ids
    //     $categoryIds = array();

    //     // get custom fields from NS response

    //     $customFields = $pro->customFieldList->customField;
    //     $imagePath = '';
    //     foreach ($customFields as $customField) {
    //         // get store from netsuite
    //         if ($customField->scriptId == 'custitem_catalog_assignment') {
    //             if ($customField->value->name == 'Default') {
    //                 // Assigning Category
    //                 $storeIds = [
    //                     1
    //                 ];
    //             } else if ($customField->value == 'FirstNet') {
    //                 // Assigning Category
    //                 $storeIds = [
    //                     3
    //                 ];
    //             }
    //         }

    //         // get category id from netsuite
    //         if ($customField->scriptId == 'custitem_mcg_item_category_id') {
    //             if ($customField->value->name == '3') {
    //                 $categoryIds = [
    //                     10
    //                 ];
    //             } else if ($customField->value->name == '7') {
    //                 $categoryIds = [
    //                     11
    //                 ];
    //             } else {
    //                 $categoryIds = [
    //                     $customField->value->name
    //                 ];
    //             }
    //         }

    //         //get image url from netsuite and upload it into media directory
    //         if ($customField->scriptId == 'custitem_thumbnail_url') {
    //             // image name
    //             $fileName = 'product_image.jpg';
    //             $file = $customField->value;
    //             $file_headers = @get_headers($file);

    //             if ($file_headers && $file_headers[0] == 'HTTP/1.0 200 OK') {
    //                 //image upload code to media/NS_Product_Images directory
    //                 copy($customField->value, $root . '/pub/media/NS_Product_Images/' . $fileName);
    //                 $imagePath = $root . '/pub/media/NS_Product_Images/' . $fileName; // path of the image
    //             }
    //         }
    //     }

    //     // product data from netsuite response
    //     $data = array();
    //     $data = array(
    //         'itemId' => $pro->itemId,
    //         'internalId' => $pro->internalId,
    //         'displayName' => $pro->displayName ? $pro->displayName : 'item',
    //         'stockDescription' => $pro->stockDescription ? $pro->salesDescription : '',
    //         'salesDescription' => $pro->salesDescription ? $pro->salesDescription : '',
    //         'attributeSetId' => 4,
    //         'status' => 1,
    //         'weight' => $pro->weight ? $pro->weight : '1',
    //         'visibility' => 4,
    //         'taxClassId' => 0,
    //         'typeId' => 'simple',
    //         'cost' => $pro->cost ? $pro->cost : '0',
    //         'manufacturer' => $pro->manufacturer ? $pro->manufacturer : '',
    //         'websiteIds' => $storeIds,
    //         'storeIds' => [],
    //         'categoryIds' => $categoryIds,
    //         'imagePath' => $imagePath
    //     );
    //     // adding product in Magento
    //     try {
    //         $ProductFactory = $this->product->create();
    //         // product sku
    //         $ProductFactory->setSku($data['itemId']);
    //         // Set your internal id from netsuite here
    //         $ProductFactory->setItemNsInternalId($data['internalId']);
    //         // Name of Product 
    //         $ProductFactory->setName($data['displayName']);
    //         // short description of Product
    //         $ProductFactory->setShortDescription($data['stockDescription']);
    //         //  description of Product
    //         $ProductFactory->setDescription($data['salesDescription']);
    //         // Attribute set id
    //         $ProductFactory->setAttributeSetId(4);
    //         // Status on product enabled/ disabled 1/0
    //         $ProductFactory->setStatus($data['status']);
    //         // weight of product
    //         $ProductFactory->setWeight($data['weight']);
    //         // visibilty of product (catalog / search / catalog, search / Not visible individually)
    //         $ProductFactory->setVisibility($data['visibility']);
    //         // Tax class id
    //         $ProductFactory->setTaxClassId($data['taxClassId']);
    //         // type of product (simple/virtual/downloadable/configurable)
    //         $ProductFactory->setTypeId($data['typeId']);
    //         // price of product
    //         $ProductFactory->setPrice($data['cost']);
    //         // product quantity
    //         $ProductFactory->setStockData(
    //             array(
    //                 'use_config_manage_stock' => 0,
    //                 'manage_stock' => 0,
    //                 'is_in_stock' => 1,
    //                 'qty' => 0
    //             )
    //         );
    //         // manufacturer of product
    //         $ProductFactory->setManufacturer($data['manufacturer']);
    //         // assignig product to website
    //         $ProductFactory->setWebsiteIds($data['websiteIds']);
    //         // assigning store ids
    //         $ProductFactory->setStoreIds($data['storeIds']);
    //         // assigning category ids
    //         $ProductFactory->setCategoryIds($data['categoryIds']);
    //         if ($data['imagePath']) {
    //             // add product image
    //             $ProductFactory->addImageToMediaGallery($imagePath, array('image', 'small_image', 'thumbnail'), false, false);
    //             // Image deleting code from media/NS_Product_Images directory
    //             if ($this->_file->isExists($imagePath)) {
    //                 $this->_file->deleteFile($imagePath);
    //             }
    //         }
    //         $ProductFactory->save();
    //         // add custom options to product
    //         $newProductId = $ProductFactory->getId();
    //         // set product locations 
    //         $locations = array();
    //         foreach ($pro->locationsList->locations as $loc) {
    //             try {
    //                 $locations[] = $loc->locationId->internalId;
    //                 $itemLocation = $this->itemLocation->create();
    //                 $itemLocation->setData('item_id', $newProductId);
    //                 $itemLocation->setData('location_name', $loc->location);
    //                 $itemLocation->setData('item_internal_id', $ProductFactory->getItemNsInternalId());
    //                 $itemLocation->setData('location_id', $loc->locationId->internalId);
    //                 $itemLocation->setData('name', $loc->locationId->name);
    //                 $itemLocation->setData('qty', 10);
    //                 $itemLocation->save();
    //             } catch (\Exception $e) {
    //             }
    //         }
    //         if ($locations) {
    //             if (in_array('1', $ProductFactory->getWebsiteIds())) {
    //                 $loc_ids = implode(',', $locations);
    //                 $ProductFactory->setStoreId(1);
    //                 $ProductFactory->setLocationId($loc_ids);
    //             }
    //         }
    //         $ProductFactory->save();

    //         if ($newProductId) {
    //             $this->addProductOptions($newProductId, $storeIds, $categoryIds);
    //         }
    //        // echo "Product with sku: " . $pro->itemId . " added successfully!\n";
    //         $logger->info(__('Product with sku: ' . $pro->itemId . ' added successfully!'));
    //         return;
    //     } catch (\NoSuchEntityException $ex) {
    //         $logger->debug(__('AscentDigital\NetsuiteConnector\Cron\Item\UpdateProducts' . "No such entity exist with sku: " . $pro->itemId));
    //         return;
    //     } catch (\Exception $ex) {
    //         $logger->debug(__('AscentDigital\NetsuiteConnector\Cron\Item\UpdateProducts' . $ex->getMessage() . ' with sku: ' . $pro->itemId));
    //         return;
    //     }
    // }

    // public function addProductOptions($productId, $storeIds, $categoryIds)
    // {
    //     $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

    //     $allowedCategories = array(10, 11, 12, 13);
    //     $common = array_intersect($allowedCategories, $categoryIds);

    //     $ePPTvalues = [
    //         [
    //             'record_id' => 0,
    //             'title' => 'AT&T ePTT',
    //             'price' => '',
    //             'price_type' => "fixed",
    //             'sort_order' => 1,
    //             'is_delete' => 0
    //         ],
    //         [
    //             'record_id' => 1,
    //             'title' => 'FNPTT',
    //             'price' => '',
    //             'price_type' => "fixed",
    //             'sort_order' => 2,
    //             'is_delete' => 0
    //         ],
    //         [
    //             'record_id' => 2,
    //             'title' => 'MCPTT',
    //             'price' => '',
    //             'price_type' => "fixed",
    //             'sort_order' => 3,
    //             'is_delete' => 0
    //         ]
    //     ];

    //     if (in_array(3, $storeIds) && count($common) > 0) {

    //         if (in_array(13, $categoryIds)) {
    //             $options = [
    //                 [
    //                     "sort_order"    => 1,
    //                     "title"         => "User Name",
    //                     "price_type"    => "fixed",
    //                     "price"         => "",
    //                     "type"          => "field",
    //                     "is_require"    => 0
    //                 ], [
    //                     "sort_order"    => 2,
    //                     "title"         => "Manufacturer",
    //                     "price_type"    => "fixed",
    //                     "price"         => "",
    //                     "type"          => "field",
    //                     "is_require"    => 0
    //                 ], [
    //                     "sort_order"    => 3,
    //                     "title"         => "Model",
    //                     "price_type"    => "fixed",
    //                     "price"         => "",
    //                     "type"          => "field",
    //                     "is_require"    => 0
    //                 ], [
    //                     "sort_order"    => 4,
    //                     "title"         => "IMEI",
    //                     "price_type"    => "fixed",
    //                     "price"         => "",
    //                     "type"          => "field",
    //                     "is_require"    => 0
    //                 ], [
    //                     "sort_order"    => 5,
    //                     "title"         => "ICCID",
    //                     "price_type"    => "fixed",
    //                     "price"         => "",
    //                     "type"          => "field",
    //                     "is_require"    => 0
    //                 ], [
    //                     "sort_order"    => 6,
    //                     "title"         => "ePPT options",
    //                     "price_type"    => "fixed",
    //                     "price"         => "",
    //                     "type"          => "drop_down",
    //                     "is_require"    => 0,
    //                     "values"        => $ePPTvalues
    //                 ]
    //             ];
    //         } else {
    //             $options = [
    //                 [
    //                     "sort_order"    => 1,
    //                     "title"         => "User Name",
    //                     "price_type"    => "fixed",
    //                     "price"         => "",
    //                     "type"          => "field",
    //                     "is_require"    => 0
    //                 ], [
    //                     "sort_order"    => 2,
    //                     "title"         => "ePPT options",
    //                     "price_type"    => "fixed",
    //                     "price"         => "",
    //                     "type"          => "drop_down",
    //                     "is_require"    => 0,
    //                     "values"        => $ePPTvalues
    //                 ]
    //             ];
    //         }

    //         $product = $this->_product->load($productId);
    //         $productId = $product->getId();

    //         $product->setHasOptions(1);
    //         $product->setCanSaveCustomOptions(true);
    //         // foreach ($options as $arrayOption) {
    //         //     $option = $this->_option
    //         //             ->setProductId($productId)
    //         //             ->setStoreId($product->getStoreId())
    //         //             ->addData($arrayOption);
    //         //     $option->save();
    //         //     $product->addOption($option);
    //         // }

    //         //using Object Managet because $this->option not working correctly, only adding the last option
    //         $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    //         foreach ($options as $arrayOption) {
    //             $option = $objectManager->create('\Magento\Catalog\Model\Product\Option')
    //                 ->setProductId($productId)
    //                 ->setStoreId($product->getStoreId())
    //                 ->addData($arrayOption);
    //             $option->save();
    //             $product->addOption($option);
    //         }
    //         $product->save();
    //     }
    //     return;
    // }


}
