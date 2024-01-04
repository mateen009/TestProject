<?php

namespace AscentDigital\NetsuiteConnector\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use AscentDigital\NetsuiteConnector\Model\NSCronFactory;
use Magento\Framework\Filesystem\DirectoryList as Directory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\Filesystem\Driver\File;
use AscentDigital\NetsuiteConnector\Model\SaveItemDetailsFactory as ItemLocation;
use AscentDigital\NetsuiteConnector\Helper\ProductImageHelper;

class ProductHelper extends AbstractHelper
{
    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $directory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $product;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var \Magento\Catalog\Model\Product\Option
     */
    protected $_option;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $_file;
    /**
     * @var \AscentDigital\NetsuiteConnector\Model\NSCronFactory
     */
    protected $nsCron;
    protected $itemLocation;
    protected $productImageHelper;

    public function __construct(
        Directory $directory,
        ProductFactory $product,
        ProductImageHelper $productImageHelper,
        Product $_product,
        Option $_option,
        ItemLocation $itemLocation,
        NSCronFactory $nsCron,
        File $file
    ) {
        $this->directory = $directory;
        $this->product = $product;
        $this->productImageHelper = $productImageHelper;
        $this->_product = $_product;
        $this->_option = $_option;
        $this->_file = $file;
        $this->nsCron = $nsCron;
        $this->itemLocation = $itemLocation;
    }

    /**
     * Get Product by Sku from netsuite.
     */

    public function getProductBySku($sku, $root, $logger)
    {
        // $sku = '77-56603-ARIBA';
        $service = new \NetSuiteService();

        $service->setSearchPreferences(true, 20);

        $skuSearchField = new \SearchStringField();
        $skuSearchField->operator = "is";
        $skuSearchField->searchValue = $sku;
        $searchValueArray = [];
        $searchValue1 = new \ListOrRecordRef();
        $searchValue1->internalId = 1;
        $searchValue2 = new \ListOrRecordRef();
        $searchValue2->internalId = 2;
        $searchValue3 = new \ListOrRecordRef();
        $searchValue3->internalId = 3;
        // $searchValue2->name = 'FirstNet';
        $searchValueArray = [$searchValue1, $searchValue2, $searchValue3];

        $catValueArray = [];
        // magento id 3
        $catValue1 = new \ListOrRecordRef();
        $catValue1->internalId = '1';

        // magento id 7
        $catValue2 = new \ListOrRecordRef();
        $catValue2->internalId = '2';

        // magento id 12
        $catValue3 = new \ListOrRecordRef();
        $catValue3->internalId = '3';

        // magento id 13
        $catValue4 = new \ListOrRecordRef();
        $catValue4->internalId = '4';

        // magento id 14
        $catValue5 = new \ListOrRecordRef();
        $catValue5->internalId = '6';
        $catValueArray = [$catValue1, $catValue2, $catValue3, $catValue4, $catValue5];

        //custom field "custitem_mcg_item_category_id"
        $custitem_mcg_item_category_id = new \SearchMultiSelectCustomField();
        $custitem_mcg_item_category_id->operator = 'anyOf';
        $custitem_mcg_item_category_id->scriptId = 'custitem_mcg_item_category_id';
        $custitem_mcg_item_category_id->searchValue = $catValueArray;

        // custom field "custitem_catalog_assignment"
        $custitem_catalog_assignment = new \SearchMultiSelectCustomField();
        $custitem_catalog_assignment->operator = 'anyOf';
        $custitem_catalog_assignment->scriptId = 'custitem_catalog_assignment';
        $custitem_catalog_assignment->searchValue = $searchValueArray;
        $customFieldList = new \SearchCustomFieldList();
        // $customFieldList->customField = [$custitem_mcg_item_category_id, $custitem_catalog_assignment];
        $customFieldList->customField = [$custitem_catalog_assignment];

        // creating object of ItemSearchBasic class
        $itemSearch = new \ItemSearchBasic();
        //applying search condition on field
        $itemSearch->itemId = $skuSearchField;
        // $itemSearch->internalId = $skuSearchField;

        $itemSearch->customFieldList = $customFieldList;

        // creating request
        $request = new \SearchRequest();
        $request->searchRecord = $itemSearch;

        //make soap call of the created request
        $searchResponse = $service->search($request);


        // echo "<pre>";
        // print_r($searchResponse);
        // die;
        if (!$searchResponse->searchResult->status->isSuccess) {
            $messages = $searchResponse->searchResult->status->statusDetail;
            foreach ($messages as $message) {
                $logger->debug(__('Error while searching item with sku: ' . $sku . ' Message: ' . $message->message));
            }
        } else {
            if ($searchResponse->searchResult->totalRecords > 0) {
                $product = $searchResponse->searchResult->recordList->record[0];
                $productFactory = $this->product->create();
                $prod = $productFactory->loadByAttribute('sku', $product->itemId);
                // update product in magento if exist otherwise create new product
                if ($prod) {
                    $this->updateProduct($prod, $product, $root, $logger, 1);
                } else {
                    // add product in magento 
                    $this->addProduct($product, $root, $logger, 1);
                }
            }
        }
    }

    /**
     * get products from Netsuite which are added or updated in last 24 hrs 
     */

    public function getUpdatedProducts($root, $logger)
    {
        $service = new \NetSuiteService();
        $service->setSearchPreferences(false, 50);
        $cron = $this->nsCron->create()->load('update_product_cron', 'title');
        if ($cron->getData()) {
            if ($cron->getIndex() > 1) {
                $SearchMoreWithIdRequest = new \SearchMoreWithIdRequest();
                // assigning search id 
                $SearchMoreWithIdRequest->searchId = $cron->getSearchId();

                //assigning next page index
                $SearchMoreWithIdRequest->pageIndex = $cron->getIndex();

                // search next page result on the basis of search id
                $searchResponse = $service->searchMoreWithId($SearchMoreWithIdRequest);

                $searchResult = $searchResponse->searchResult;
                $totalPages = $searchResult->totalPages;
                $pageIndex = $searchResponse->searchResult->pageIndex;
                if ($pageIndex < $totalPages) {
                    $cron->setIndex($pageIndex + 1);
                    $cron->setTotalPages($totalPages);
                    $cron->setTitle('update_product_cron');
                    $cron->setSearchId($searchResult->searchId);
                    $cron->save();
                } else {
                    $cron->setIndex(0);
                    $cron->setTotalPages('');
                    $cron->setTitle('update_product_cron');
                    $cron->setSearchId('');
                    $cron->save();
                }
            } else {
                $searchResponse = $this->searchItemsInNetsuite($service, $cron);
            }
        } else {
            $searchResponse = $this->searchItemsInNetsuite($service, $cron);
        }

        $this->processSearchResponse($searchResponse, $logger, $root);
    }

    public function searchItemsInNetsuite($service, $cron)
    {
        $date = date("Y-m-d"); //date
        $time = date("H:i:s"); //time        
        $current_date = $date . 'T' . $time;     // current date with time
        $before_24hrs = date("Y-m-d", strtotime($date . '- 11 months')) . 'T' . $time;   //date and time before 24hrs
        // search condition
        $itemSearchField = new \SearchDateField();
        $itemSearchField->operator = "within";
        $itemSearchField->searchValue = $before_24hrs;
        $itemSearchField->searchValue2 = $current_date;
        //custom field "custitem_mcg_item_category_id"
        //

        $searchValueArray = [];
        $searchValue1 = new \ListOrRecordRef();
        $searchValue1->internalId = 1;
        $searchValue2 = new \ListOrRecordRef();
        $searchValue2->internalId = 2;
        // $searchValue2->name = 'FirstNet';
        $searchValue3 = new \ListOrRecordRef();
        $searchValue3->internalId = 3;
        // $searchValueArray = [$searchValue1, $searchValue2, $searchValue3];
        $searchValueArray = [$searchValue3];

        $catValueArray = [];
        $catValue1 = new \ListOrRecordRef();
        $catValue1->internalId = '1';

        $catValue2 = new \ListOrRecordRef();
        $catValue2->internalId = '2';

        $catValue3 = new \ListOrRecordRef();
        $catValue3->internalId = '3';

        $catValue4 = new \ListOrRecordRef();
        $catValue4->internalId = '4';

        // magento id 14
        $catValue5 = new \ListOrRecordRef();
        $catValue5->internalId = '6';
        // magento id 61
        $catValue6 = new \ListOrRecordRef();
        $catValue6->internalId = '1';
        $catValueArray = [$catValue1, $catValue2, $catValue3, $catValue4, $catValue5, $catValue6];

        //custom field "custitem_mcg_item_category_id"
        $custitem_mcg_item_category_id = new \SearchMultiSelectCustomField();
        $custitem_mcg_item_category_id->operator = 'anyOf';
        $custitem_mcg_item_category_id->scriptId = 'custitem_mcg_item_category_id';
        $custitem_mcg_item_category_id->searchValue = $catValueArray;

        // custom field "custitem_catalog_assignment"
        $custitem_catalog_assignment = new \SearchMultiSelectCustomField();
        $custitem_catalog_assignment->operator = 'anyOf';
        $custitem_catalog_assignment->scriptId = 'custitem_catalog_assignment';
        $custitem_catalog_assignment->searchValue = $searchValueArray;
        $customFieldList = new \SearchCustomFieldList();
        // $customFieldList->customField = [$custitem_mcg_item_category_id, $custitem_catalog_assignment];
        $customFieldList->customField = [$custitem_catalog_assignment];

        //
        // die('mtenn');
        // creating object of ItemSearchBasic class
        $itemSearch = new \ItemSearchBasic();
        //applying search condition on field
        $itemSearch->lastModifiedDate = $itemSearchField;
        $itemSearch->customFieldList = $customFieldList;

        // creating request
        $request = new \SearchRequest();
        $request->searchRecord = $itemSearch;

        //make soap call of the created request
        $searchResponse = $service->search($request);
        $searchResult = $searchResponse->searchResult;
        $totalPages = $searchResult->totalPages;
        $pageIndex = $searchResponse->searchResult->pageIndex;
        if ($totalPages > 1) {
            if ($cron->getData()) {
                $cron->setIndex($pageIndex + 1);
                $cron->setTotalPages($totalPages);
                $cron->setTitle('update_product_cron');
                $cron->setSearchId($searchResult->searchId);
                $cron->save();
            } else {
                $cron = $this->nsCron->create();
                $cron->setIndex($pageIndex + 1);
                $cron->setTotalPages($totalPages);
                $cron->setTitle('update_product_cron');
                $cron->setSearchId($searchResult->searchId);
                $cron->save();
            }
        }
        // echo "<pre>";
        // print_r($searchResponse);
        // die;

        return $searchResponse;
    }

    public function processSearchResponse($searchResponse, $logger, $root)
    {

        if (!$searchResponse->searchResult->status->isSuccess) {
            $messages = $searchResponse->searchResult->status->statusDetail;
            foreach ($messages as $message) {
                $logger->info($message->message);
                //  $this->messageManager->addError(__($message->message));
            }
        } else {
            if ($searchResponse->searchResult->totalRecords > 0) {
                $products = $searchResponse->searchResult->recordList->record;
                foreach ($products as $key => $product) {
                    // echo "<pre>";
                    // print_r($product);die;
                    $productFactory = $this->product->create();
                    $prod = $productFactory->loadByAttribute('sku', $product->itemId);
                    // update product in magento if exist otherwise create new product
                    if ($prod) {
                        $this->updateProduct($prod, $product, $root, $logger, $key);
                    } else {
                        // add product in magento 
                        $this->addProduct($product, $root, $logger, $key);
                    }
                }
            } else {
                // set index and search id to null
            }
        }
    }
    public function updateProduct($product, $ns_product, $root, $logger, $key)
    {

        //update product quantity
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $productRepository = $objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');
            $prod = $productRepository->get(
                $ns_product->itemId,
                true/* edit mode */,
                0/* global store*/,
                true/* force reload*/
            );
            if ($prod && $ns_product->isInactive == 1) {
                $prod->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                $prod->save();
                return;
            }
            $priceValue = 0;
            $pricing = $ns_product->pricingMatrix->pricing;
            foreach ($pricing as $price) {
                $newprice = $price->priceList->price;
                foreach ($newprice as $pr) {
                    $priceValue = $pr->value;
                    break;
                }
                break;
            }

            $product->setStockData(
                array(
                    'use_config_manage_stock' => 0,
                    'manage_stock' => 1,
                    'is_in_stock' => 1,
                    'qty' => 200
                )
            );

            $product->setData('classification_id', $ns_product->class->name);
            $product->setData('mfg_part_number', $ns_product->displayName);
            $product->setPrice($priceValue);


            $logger->debug(__('update startsssss sku' . $ns_product->itemId));
            // $product->save();
            // $logger->debug(__('update enddddssssssss sku' . $ns_product->itemId));
            // return;
            $product->addAttributeUpdate('short_description', $ns_product->salesDescription ? $ns_product->salesDescription : '', '0');
            $product->addAttributeUpdate('short_description', $ns_product->salesDescription ? $ns_product->salesDescription : '', '2');
            $product->setItemNsInternalId($ns_product->internalId);
            $manufacturer = isset($ns_product->manufacturer) ? $ns_product->manufacturer : '0';
            if ($manufacturer == 'Apple') {
                $product->setBrand(2);
            } elseif ($manufacturer == 'Samsung') {
                $product->setBrand(1);
            }
            // image 
            $customFields = $ns_product->customFieldList->customField;
            $imagePath = '';
            $categoryIds = array();
            $action = $objectManager->create('Magento\Catalog\Model\Product\Action');
            foreach ($customFields as $customField) {
                if ($customField->scriptId == 'custitem_catalog_assignment') {
                    // updating name
                    if ($customField->value->name == 'Default') {
                        // Assigning website and store ids
                        $storeId = 1;
                        $action->updateAttributes([$product->getId()], ['name' => isset($ns_product->displayName) ? $ns_product->displayName : 'item name'], $storeId);
                    } else if ($customField->value->name == 'FirstNet') {
                        // Assigning website and store ids
                        $storeId = 2;
                        $action->updateAttributes([$product->getId()], ['name' => isset($ns_product->displayName) ? $ns_product->displayName : 'item name'], $storeId);
                    } else if ($customField->value->name == 'Ariba') {
                        // Assigning website and store ids
                        $storeId = 5;
                        $action->updateAttributes([$product->getId()], ['name' => isset($ns_product->displayName) ? $ns_product->displayName : 'item name'], $storeId);
                    }
                }
                // get custitem_unspsc field from netsuite
                $custitem_unspsc = '';
                if ($customField->scriptId == 'custitemunspsc') {
                    $unspsc = $customField->value;
                    if ($unspsc) {
                        $custitem_unspsc = $unspsc->name;
                        $product->setData('UNSPSC', $custitem_unspsc);
                    }
                }

                // get category id from netsuite
                if ($customField->scriptId == 'custitem_mcg_item_category_id') {
                    if ($customField->value->name == '3') {
                        $categoryIds = [
                            10, 62
                        ];
                    } else if ($customField->value->name == '7') {
                        $categoryIds = [
                            11, 62
                        ];
                    } else if ($customField->value->name == '37') {
                        $categoryIds = [
                            7, 62
                        ];
                    } else {
                        $categoryIds = [
                            $customField->value->name, 62
                        ];
                    }
                }
                //get image url from netsuite and upload it into media directory
                if ($customField->scriptId == 'custitem_thumbnail_url') {
                    // image name
                    $fileName = preg_replace('|/|', '', $ns_product->displayName) . 'image' . $key . '.jpg';
                    $file = $customField->value;
                    $file_headers = @get_headers($file);

                    if ($file_headers && $file_headers[0] == 'HTTP/1.1 200 OK') {
                        //image upload code to media/NS_Product_Images directory
                        copy($customField->value, $root . '/pub/media/NS_Product_Images/' . $fileName);
                        $imagePath = $root . '/pub/media/NS_Product_Images/' . $fileName; // path of the image
                    }
                }
            }
            $product->setCategoryIds($categoryIds);
            $logger->debug(__('update start sku' . $ns_product->itemId));
            $product->save();
            if ($imagePath) {
                // add product image
                $this->productImageHelper->addImage($product, $imagePath, $fileName, $logger);
            }
            // // image 
            // $locations = array();
            // $locations = array();
            // if (isset($ns_product->locationsList)) {
            //     foreach ($ns_product->locationsList->locations as $loc) {
            //         try {
            //             $locations[] = $loc->locationId->internalId;
            //             $itemLocation = $this->itemLocation->create();
            //             $itemLocation->setData('item_id', $product->getId());
            //             $itemLocation->setData('location_name', $loc->location);
            //             $itemLocation->setData('item_internal_id', $product->getItemNsInternalId());
            //             $itemLocation->setData('location_id', $loc->locationId->internalId);
            //             $itemLocation->setData('name', $loc->locationId->name);
            //             $itemLocation->setData('qty', 10);
            //             $itemLocation->save();
            //         } catch (\Exception $ex) {
            //             $logger->debug(__($ex->getMessage() . ' while saving location of item with sku: ' . $ns_product->itemId));
            //         }
            //     }
            // }
            // // print_r($locations);
            // if ($locations) {
            //     if (in_array('1', $product->getWebsiteIds())) {
            //         $loc_ids = implode(',', $locations);
            //         $product->setStoreId(1);
            //         $product->setLocationId($loc_ids);
            //     }
            // }

            // $product->save();
            // print_r($ns_product->itemId);
            // die('mateen');
            $logger->info(__('Product with sku: ' . $ns_product->itemId . ' updated successfully!'));
            return;
        } catch (\Exception $ex) {
            print_r($ex->getMessage());
            $logger->debug(__('AscentDigital\NetsuiteConnector\Controller\Adminhtml\Cron\UpdateProducts' . $ex->getMessage() . ' with sku: ' . $ns_product->itemId));
            return;
        }
    }
    public function addProduct($pro, $root, $logger, $key)
    {
        //intitializing store ids
        $storeIds = array();

        //intitializing website ids
        $websiteIds = array();

        //intitializing store ids
        $categoryIds = array();
        $categoryIds[] =

            // get custom fields from NS response
            $priceValue = 0;
        $pricing = $pro->pricingMatrix->pricing;
        foreach ($pricing as $price) {
            $newprice = $price->priceList->price;
            foreach ($newprice as $pr) {
                $priceValue = $pr->value;
                break;
            }
            break;
        }

        $customFields = $pro->customFieldList->customField;
        $imagePath = '';
        $custitem_unspsc = '';
        foreach ($customFields as $customField) {
            // get store from netsuite
            if ($customField->scriptId == 'custitem_catalog_assignment') {
                if ($customField->value->name == 'Default') {
                    // Assigning website and store ids
                    $storeIds = [
                        1
                    ];
                    $websiteIds = [
                        1
                    ];
                } else if ($customField->value->name == 'FirstNet') {
                    // Assigning website and store ids
                    $storeIds = [
                        2
                    ];
                    $websiteIds = [
                        3
                    ];
                } else if ($customField->value->name == 'Ariba') {
                    // Assigning website and store ids
                    $storeIds = [
                        5
                    ];
                    $websiteIds = [
                        4
                    ];
                }
            }

            // get custitem_unspsc field from netsuite
            if ($customField->scriptId == 'custitemunspsc') {
                $unspsc = $customField->value;
                if ($unspsc) {
                    $custitem_unspsc = $unspsc->name;
                }
            }

            // get category id from netsuite
            if ($customField->scriptId == 'custitem_mcg_item_category_id') {
                if ($customField->value->name == '3') {
                    $categoryIds = [
                        10, 62
                    ];
                } else if ($customField->value->name == '7') {
                    $categoryIds = [
                        11, 62
                    ];
                } else if ($customField->value->name == '37') {
                    $categoryIds = [
                        7, 62
                    ];
                } else {
                    $categoryIds = [
                        $customField->value->name, 62
                    ];
                }
            }

            //get image url from netsuite and upload it into media directory
            if ($customField->scriptId == 'custitem_thumbnail_url') {
                // image name
                $fileName = preg_replace('|/|', '', $pro->displayName) . 'image' . $key . '.jpg';
                // $fileName = $pro->displayName . '.jpg';
                $file = $customField->value;
                $file_headers = @get_headers($file);

                if ($file_headers && $file_headers[0] == 'HTTP/1.0 200 OK') {
                    //image upload code to media/NS_Product_Images directory
                    copy($customField->value, $root . '/pub/media/NS_Product_Images/' . $fileName);
                    $imagePath = $root . '/pub/media/NS_Product_Images/' . $fileName; // path of the image
                }
            }
        }

        // product data from netsuite response
        $data = array();
        $data = array(
            'itemId' => $pro->itemId,
            'internalId' => $pro->internalId,
            'custitem_unspsc' => $custitem_unspsc,
            'displayName' => isset($pro->displayName) ? $pro->displayName : 'item name',
            'stockDescription' => isset($pro->stockDescription) ? $pro->stockDescription : (isset($pro->salesDescription) ? $pro->salesDescription : ''),
            'salesDescription' => isset($pro->salesDescription) ? $pro->salesDescription : '',
            'attributeSetId' => 4,
            'status' => 1,
            'weight' => isset($pro->weight) ? $pro->weight : '1',
            'visibility' => 4,
            'taxClassId' => 0,
            'typeId' => 'simple',
            'cost' => isset($priceValue),
            'manufacturer' => isset($pro->manufacturer) ? $pro->manufacturer : '',
            'brand' => isset($pro->manufacturer) ? $pro->manufacturer : '0',
            'websiteIds' => $websiteIds,
            'storeIds' => $storeIds,
            'categoryIds' => $categoryIds,
            'imagePath' => $imagePath
        );
        // adding product in Magento
        try {
            $ProductFactory = $this->product->create();
            // product sku
            $ProductFactory->setSku($data['itemId']);
            // Set your internal id from netsuite here
            $ProductFactory->setItemNsInternalId($data['internalId']);
            // Set your custitem_unspsc from netsuite here
            $ProductFactory->setUNSPSC($data['custitem_unspsc']);
            // Name of Product 
            $ProductFactory->setName($data['displayName']);
            // short description of Product
            $ProductFactory->setShortDescription($data['stockDescription']);
            //  description of Product
            $ProductFactory->setDescription($data['salesDescription']);
            // Attribute set id
            $ProductFactory->setAttributeSetId(4);
            // Status on product enabled/ disabled 1/0
            $ProductFactory->setStatus($data['status']);
            // weight of product
            $ProductFactory->setWeight($data['weight']);
            // visibilty of product (catalog / search / catalog, search / Not visible individually)
            $ProductFactory->setVisibility($data['visibility']);
            // Tax class id
            $ProductFactory->setTaxClassId($data['taxClassId']);
            // type of product (simple/virtual/downloadable/configurable)
            $ProductFactory->setTypeId($data['typeId']);
            // price of product
            $ProductFactory->setPrice($data['cost']);
            // product quantity
            $ProductFactory->setStockData(
                array(
                    'use_config_manage_stock' => 0,
                    'manage_stock' => 0,
                    'is_in_stock' => 1,
                    'qty' => 0
                )
            );
            // manufacturer of product
            $ProductFactory->setManufacturer($data['manufacturer']);
            // brand of product 
            if ($data['brand'] == 'Apple') {
                $ProductFactory->setBrand(2);
            } elseif ($data['brand'] == 'Samsung') {
                $ProductFactory->setBrand(1);
            }
            // assignig product to website
            $ProductFactory->setWebsiteIds($data['websiteIds']);
            // assigning store ids
            $ProductFactory->setStoreIds($data['storeIds']);
            // assigning category ids
            $ProductFactory->setCategoryIds($data['categoryIds']);
            if ($data['imagePath']) {
                // add product image
                $ProductFactory->addImageToMediaGallery($imagePath, array('image', 'small_image', 'thumbnail'), false, false);
                // Image deleting code from media/NS_Product_Images directory
                if ($this->_file->isExists($imagePath)) {
                    $this->_file->deleteFile($imagePath);
                }
            }
            $ProductFactory->save();
            // add custom options to product
            $newProductId = $ProductFactory->getId();
            // set product locations 
            $locations = array();
            if (isset($pro->locationsList)) {
                foreach ($pro->locationsList->locations as $loc) {
                    try {
                        $locations[] = $loc->locationId->internalId;
                        $itemLocation = $this->itemLocation->create();
                        $itemLocation->setData('item_id', $newProductId);
                        $itemLocation->setData('location_name', $loc->location);
                        $itemLocation->setData('item_internal_id', $ProductFactory->getItemNsInternalId());
                        $itemLocation->setData('location_id', $loc->locationId->internalId);
                        $itemLocation->setData('name', $loc->locationId->name);
                        $itemLocation->setData('qty', 10);
                        $itemLocation->save();
                    } catch (\Exception $e) {
                        $logger->debug(__($e->getMessage() . ' while saving location of item with sku: ' . $pro->itemId));
                    }
                }
            }
            if ($locations) {
                if (in_array('1', $ProductFactory->getWebsiteIds())) {
                    $loc_ids = implode(',', $locations);
                    $ProductFactory->setStoreId(1);
                    $ProductFactory->setLocationId($loc_ids);
                }
            }
            $ProductFactory->save();

            if ($newProductId) {
                $this->addProductOptions($newProductId, $storeIds, $categoryIds);
            }
            $logger->info(__('Product with sku: ' . $pro->itemId . ' added successfully!'));
            return;
        } catch (\NoSuchEntityException $ex) {
            $logger->debug(__("No such entity exist with sku: " . $pro->itemId));
            return;
        } catch (\Exception $ex) {
            $logger->debug(__($ex->getMessage() . ' with sku: ' . $pro->itemId));
            return;
        }
    }

    public function addProductOptions($productId, $storeIds, $categoryIds)
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $allowedCategories = array(10, 11, 12, 13);
        $common = array_intersect($allowedCategories, $categoryIds);

        $ePPTvalues = [
            [
                'record_id' => 0,
                'title' => 'AT&T ePTT',
                'price' => '',
                'price_type' => "fixed",
                'sort_order' => 1,
                'is_delete' => 0
            ],
            [
                'record_id' => 1,
                'title' => 'FNPTT',
                'price' => '',
                'price_type' => "fixed",
                'sort_order' => 2,
                'is_delete' => 0
            ],
            [
                'record_id' => 2,
                'title' => 'MCPTT',
                'price' => '',
                'price_type' => "fixed",
                'sort_order' => 3,
                'is_delete' => 0
            ]
        ];

        if (in_array(3, $storeIds) && count($common) > 0) {

            if (in_array(13, $categoryIds)) {
                $options = [
                    [
                        "sort_order"    => 1,
                        "title"         => "User Name",
                        "price_type"    => "fixed",
                        "price"         => "",
                        "type"          => "field",
                        "is_require"    => 0
                    ], [
                        "sort_order"    => 2,
                        "title"         => "Manufacturer",
                        "price_type"    => "fixed",
                        "price"         => "",
                        "type"          => "field",
                        "is_require"    => 0
                    ], [
                        "sort_order"    => 3,
                        "title"         => "Model",
                        "price_type"    => "fixed",
                        "price"         => "",
                        "type"          => "field",
                        "is_require"    => 0
                    ], [
                        "sort_order"    => 4,
                        "title"         => "IMEI",
                        "price_type"    => "fixed",
                        "price"         => "",
                        "type"          => "field",
                        "is_require"    => 0
                    ], [
                        "sort_order"    => 5,
                        "title"         => "ICCID",
                        "price_type"    => "fixed",
                        "price"         => "",
                        "type"          => "field",
                        "is_require"    => 0
                    ], [
                        "sort_order"    => 6,
                        "title"         => "ePPT options",
                        "price_type"    => "fixed",
                        "price"         => "",
                        "type"          => "drop_down",
                        "is_require"    => 0,
                        "values"        => $ePPTvalues
                    ]
                ];
            } else {
                $options = [
                    [
                        "sort_order"    => 1,
                        "title"         => "User Name",
                        "price_type"    => "fixed",
                        "price"         => "",
                        "type"          => "field",
                        "is_require"    => 0
                    ], [
                        "sort_order"    => 2,
                        "title"         => "ePPT options",
                        "price_type"    => "fixed",
                        "price"         => "",
                        "type"          => "drop_down",
                        "is_require"    => 0,
                        "values"        => $ePPTvalues
                    ]
                ];
            }

            $product = $this->_product->load($productId);
            $productId = $product->getId();

            $product->setHasOptions(1);
            $product->setCanSaveCustomOptions(true);
            // foreach ($options as $arrayOption) {
            //     $option = $this->_option
            //             ->setProductId($productId)
            //             ->setStoreId($product->getStoreId())
            //             ->addData($arrayOption);
            //     $option->save();
            //     $product->addOption($option);
            // }

            //using Object Managet because $this->option not working correctly, only adding the last option
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            foreach ($options as $arrayOption) {
                $option = $objectManager->create('\Magento\Catalog\Model\Product\Option')
                    ->setProductId($productId)
                    ->setStoreId($product->getStoreId())
                    ->addData($arrayOption);
                $option->save();
                $product->addOption($option);
            }
            $product->save();
        }
        return;
    }
}
