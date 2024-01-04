<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Observer\Product;

use Magenest\RentalSystem\Model\IndexerProcessor;
use Magento\Framework\App\RequestInterface;
use Magento\InventoryApi\Api\GetSourcesAssignedToStockOrderedByPriorityInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySales\Model\ResourceModel\GetAssignedStockIdForWebsite;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magenest\RentalSystem\Model\Rental;
use Magenest\RentalSystem\Model\ResourceModel\Rental as RentalResource;
use Magenest\RentalSystem\Model\RentalFactory;
use Magenest\RentalSystem\Model\RentalPriceFactory;
use Magenest\RentalSystem\Model\ResourceModel\RentalPrice as RentalPriceResource;
use Magenest\RentalSystem\Model\RentalOptionFactory;
use Magenest\RentalSystem\Model\ResourceModel\RentalOption as RentalOptionResource;
use Magenest\RentalSystem\Model\ResourceModel\RentalOption\CollectionFactory as RentalOptionCollection;
use Magenest\RentalSystem\Model\ResourceModel\RentalRule\CollectionFactory as RentalRuleCollectionFactory;
use Magenest\RentalSystem\Model\RentalOptionTypeFactory;
use Magenest\RentalSystem\Model\ResourceModel\RentalOptionType as RentalOptionTypeResource;
use Magenest\RentalSystem\Model\ResourceModel\RentalOptionType\CollectionFactory as RentalOptionTypeCollection;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ProductMetadataInterface;

class SaveAfterObserver implements ObserverInterface
{
    /** @var RequestInterface */
    protected $_request;

    /** @var RentalFactory */
    protected $_rentalFactory;

    /** @var RentalResource */
    protected $_rentalResource;

    /** @var RentalPriceFactory */
    protected $_rentalPriceFactory;

    /** @var RentalPriceResource */
    protected $_rentalPriceResource;

    /** @var RentalOptionFactory */
    protected $_rentalOptionFactory;

    /** @var RentalOptionResource */
    protected $_rentalOptionResource;

    /** @var RentalOptionCollection */
    protected $_rentalOptionCollection;

    /** @var RentalOptionTypeFactory */
    protected $_rentalOptionTypeFactory;

    /** @var RentalOptionTypeResource */
    protected $_rentalOptionTypeResource;

    /** @var RentalOptionTypeCollection */
    protected $_rentalOptionTypeCollection;

    /** @var \Magento\Framework\Message\ManagerInterface */
    protected $messageManager;

    /** @var StoreManagerInterface */
    protected $_storeManager;

    /** @var ProductMetadataInterface */
    protected $productMetadata;

    /** @var RentalRuleCollectionFactory */
    private $rentalRuleCollectionFactory;

    /** @var IndexerProcessor */
    private $rentalRuleIndexer;

    /** @var GetAssignedStockIdForWebsite */
    private $getAssignedStockIdForWebsite;

    /** @var GetProductSalableQtyInterface */
    private $getProductSalableQty;

    /** @var GetStockItemConfigurationInterface */
    private $getStockItemConfiguration;

    /** @var GetSourcesAssignedToStockOrderedByPriorityInterface */
    private $getSourcesAPI;

    /**
     * @param StoreManagerInterface $storeManager
     * @param RentalFactory $rentalFactory
     * @param RentalResource $rentalResource
     * @param IndexerProcessor $rentalRuleIndexer
     * @param RentalPriceFactory $rentalPriceFactory
     * @param RentalPriceResource $rentalPriceResource
     * @param RentalOptionFactory $rentalOptionFactory
     * @param RentalOptionResource $rentalOptionResource
     * @param RentalOptionCollection $rentalOptionCollection
     * @param RentalOptionTypeFactory $rentalOptionTypeFactory
     * @param RentalOptionTypeResource $rentalOptionTypeResource
     * @param RentalOptionTypeCollection $rentalOptionTypeCollection
     * @param RentalRuleCollectionFactory $rentalRuleCollectionFactory
     * @param GetAssignedStockIdForWebsite $getAssignedStockIdForWebsite
     * @param GetProductSalableQtyInterface $getProductSalableQty
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     * @param GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAPI
     * @param Context $context
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        RentalFactory $rentalFactory,
        RentalResource $rentalResource,
        IndexerProcessor $rentalRuleIndexer,
        RentalPriceFactory $rentalPriceFactory,
        RentalPriceResource $rentalPriceResource,
        RentalOptionFactory $rentalOptionFactory,
        RentalOptionResource $rentalOptionResource,
        RentalOptionCollection $rentalOptionCollection,
        RentalOptionTypeFactory $rentalOptionTypeFactory,
        RentalOptionTypeResource $rentalOptionTypeResource,
        RentalOptionTypeCollection $rentalOptionTypeCollection,
        RentalRuleCollectionFactory $rentalRuleCollectionFactory,
        GetAssignedStockIdForWebsite $getAssignedStockIdForWebsite,
        GetProductSalableQtyInterface $getProductSalableQty,
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAPI,
        Context $context,
        ProductMetadataInterface $productMetadata
    ) {
        $this->_storeManager                = $storeManager;
        $this->_rentalFactory               = $rentalFactory;
        $this->_rentalResource              = $rentalResource;
        $this->rentalRuleIndexer            = $rentalRuleIndexer;
        $this->_rentalPriceFactory          = $rentalPriceFactory;
        $this->_rentalPriceResource         = $rentalPriceResource;
        $this->_rentalOptionFactory         = $rentalOptionFactory;
        $this->_rentalOptionResource        = $rentalOptionResource;
        $this->_rentalOptionCollection      = $rentalOptionCollection;
        $this->_rentalOptionTypeFactory     = $rentalOptionTypeFactory;
        $this->_rentalOptionTypeResource    = $rentalOptionTypeResource;
        $this->_rentalOptionTypeCollection  = $rentalOptionTypeCollection;
        $this->rentalRuleCollectionFactory  = $rentalRuleCollectionFactory;
        $this->getAssignedStockIdForWebsite = $getAssignedStockIdForWebsite;
        $this->getProductSalableQty         = $getProductSalableQty;
        $this->getStockItemConfiguration    = $getStockItemConfiguration;
        $this->getSourcesAPI                = $getSourcesAPI;
        $this->_request                     = $context->getRequest();
        $this->messageManager               = $context->getMessageManager();
        $this->productMetadata              = $productMetadata;
    }

    /**
     * @param Observer $observer
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product       = $observer->getEvent()->getProduct();
        $productId     = $product->getId();
        $productTypeId = $product->getTypeId();
        $params        = $this->_request->getParams();

        if (isset($params['rental']) && $productTypeId == Rental::PRODUCT_TYPE) {
            $magentoVer = $this->productMetadata->getVersion();
            if (isset($params['sources']) && version_compare($magentoVer, '2.3', '>=')) {
                $totalQty = $this->checkQty($product, $params['sources']);
            } else {
                $totalQty = $product->getStockData();
                $totalQty = $totalQty['qty'] ?? 0;
            }

            $data  = $params['rental'];
            $model = $this->_rentalFactory->create();
            $this->_rentalResource->load($model, $productId, 'product_id');

            //remove existing identification in case of duplicating rental product
            if (isset($params['back']) && $params['back'] == 'duplicate' && empty($model->getData())) {
                $data = $this->handleDuplicate($data);
            }

            if (isset($data['type'])) {
                if ($data['type'] == 'local_pickup') {
                    $data['type']      = 1;
                    $data['lead_time'] = null;
                }
                if ($data['type'] == 'chosen_customer') {
                    $data['type'] = 2;
                }
            } else {
                $data['type']           = 0;
                $data['pickup_address'] = null;
            }

            $data['product_id']   = $productId;
            $data['product_name'] = $product->getName();

            // rental product qty
            $data['initial_qty'] = $totalQty;

            $data['available_qty'] = $data['initial_qty'];
            if (!$model->getQtyRented()) {
                $data['qty_rented'] = 0;
            }
            $model->addData($data);
            $this->_rentalResource->save($model);

            if (!empty($data['row'])) {
                if ($data['row'][0]['base_period'] != null) {
                    $data['row'][0]['base_period'] = $data['row'][0]['base_period']
                        . $data['row'][0]['base_period_unit'];
                }
                if ($data['row'][0]['additional_period'] != null) {
                    $data['row'][0]['additional_period'] = $data['row'][0]['additional_period']
                        . $data['row'][0]['additional_period_unit'];
                }
                $this->saveRentalPrice($data['row'], $model->getData());
            }
            if (!empty($data['additional_options'])) {
                $this->saveRentalOption($data['additional_options'], $model->getData());
            } else {
                $this->deleteAllOptions($productId);
            }

            $affectedRule = $this->rentalRuleCollectionFactory->create();
            $field[] = 'apply_all';
            $cond[] = ['eq' => 1];
            if (!empty($product->getAffectedCategoryIds())) {
                foreach ($product->getAffectedCategoryIds() as $categoryId) {
                    $field[] = 'category_ids';
                    $cond[] = ['finset' => $categoryId];
                }
            }
            $affectedRule->addFieldToFilter($field, $cond);
            if (!$this->rentalRuleIndexer->isIndexerScheduled()) {
                $this->rentalRuleIndexer->reindexList($affectedRule->getAllIds());
            } else {
                $this->rentalRuleIndexer->markIndexerAsInvalid();
            }
        }
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function handleDuplicate($data)
    {
        if (!empty($data['additional_options'])) {
            foreach ($data['additional_options'] as $keyOpt => $fieldOpt) {
                $data['additional_options'][$keyOpt]['id_option'] = '';
                if (!empty($fieldOpt['row'])) {
                    foreach ($fieldOpt['row'] as $keyType => $fieldType) {
                        $data['additional_options'][$keyOpt]['row'][$keyType]['id_type'] = '';
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @param array $priceData
     * @param array $model
     *
     * @throws \Exception
     */
    public function saveRentalPrice($priceData, $model)
    {
        $priceModel = $this->_rentalPriceFactory->create();
        $this->_rentalPriceResource->load($priceModel, $model['id'], 'rental_id');
        foreach ($priceData as $price) {
            $price['rental_id']  = $model['id'];
            $price['product_id'] = $model['product_id'];
            $price['type']       = 0;
            $priceModel->addData($price);
            $this->_rentalPriceResource->save($priceModel);
        }
    }

    /**
     * @param array $options
     * @param array $model
     *
     * @throws \Exception
     */
    public function saveRentalOption($options, $model)
    {
        foreach ($options as $option) {
            if (!isset($once)) {
                $once = true;
            }
            //delete option and its selection
            if (isset($option['is_delete']) && $option['is_delete'] == 1) {
                $this->_rentalOptionCollection->create()
                    ->addFieldToFilter('product_id', ['eq' => $model['product_id']])
                    ->addFieldToFilter('option_id', ['eq' => $option['record_id']])
                    ->walk('delete');
                $this->_rentalOptionTypeCollection->create()
                    ->addFieldToFilter('product_id', ['eq' => $model['product_id']])
                    ->addFieldToFilter('option_id', ['eq' => $option['option_id']])
                    ->walk('delete');
                continue;
            } else {
                if ($once) {
                    $currentRecord = [];
                    $oldRecord     = [];
                    foreach ($options as $optionCheck) {
                        if (empty($optionCheck['id_option'])) {
                            continue;
                        }
                        array_push($currentRecord, (int)$optionCheck['id_option']);
                    }
                    $oldOptions = $this->_rentalOptionCollection->create()
                        ->addFieldToFilter('product_id', ['eq' => $model['product_id']]);
                    foreach ($oldOptions as $oldOption) {
                        array_push($oldRecord, $oldOption['id']);
                    }

                    $toDeleteOptions = array_diff($oldRecord, $currentRecord);
                    foreach ($toDeleteOptions as $deleteOption) {
                        $optionToDelete = $this->_rentalOptionFactory->create();
                        $this->_rentalOptionResource->load($optionToDelete, $deleteOption)->delete($optionToDelete);

                        $this->deleteOptionTypes($model['product_id'], $deleteOption);
                    }
                    $once = false;
                }
            }

            $modelOption = $this->_rentalOptionFactory->create();
            if (!empty($option['id_option'])) {
                $this->_rentalOptionResource->load($modelOption, $option['id_option']);
            }
            if (strtolower($option['option_title']) == 'select') {
                $option['option_title'] = "Options";
                $this->messageManager->addNoticeMessage('Cannot save option title is "Select".');
            }

            $type = $option['type'];

            $data = [
                'rental_id'    => $model['id'],
                'product_id'   => $model['product_id'],
                'option_id'    => $option['record_id'],
                'option_title' => $option['option_title'],
                'type'         => $type,
                'is_required'  => $option['is_required'],
            ];
            $modelOption->addData($data);
            $this->_rentalOptionResource->save($modelOption);
            if (!empty($option['row'])) {
                $this->saveRentalOptionType($option['row'], $modelOption->getData());
            } else {
                $this->deleteOptionTypes($model['product_id'], $option['id_option']);
            }
        }
    }

    /**
     * @param array $types
     * @param array $modelOption
     *
     * @throws \Exception
     */
    public function saveRentalOptionType($types, $modelOption)
    {
        foreach ($types as $typeOptions) {
            if (!isset($once)) {
                $once = true;
            }
            if (isset($typeOptions['is_delete']) && $typeOptions['is_delete'] == 1) {
                $modelType = $this->_rentalOptionTypeFactory->create();
                $this->_rentalOptionTypeResource->load($modelType, $typeOptions['id'])->delete($modelType);
                continue;
            } else {
                if ($once) {
                    $currentRecord = [];
                    $oldRecord     = [];
                    foreach ($types as $typeCheck) {
                        if (empty($typeCheck['id_type'])) {
                            continue;
                        }
                        array_push($currentRecord, (int)$typeCheck['id_type']);
                    }
                    $oldOptions = $this->_rentalOptionTypeCollection->create()
                        ->addFieldToFilter('product_id', ['eq' => $modelOption['product_id']])
                        ->addFieldToFilter('option_id', ['eq' => $modelOption['id']]);
                    foreach ($oldOptions as $oldOption) {
                        array_push($oldRecord, $oldOption->getId());
                    }
                    $toDeleteOptions = array_diff($oldRecord, $currentRecord);
                    foreach ($toDeleteOptions as $deleteOption) {
                        $optionTypeToDelete = $this->_rentalOptionTypeFactory->create();
                        $this->_rentalOptionTypeResource
                            ->load($optionTypeToDelete, $deleteOption)
                            ->delete($optionTypeToDelete);
                    }

                    $once = false;
                }
            }

            $modelType = $this->_rentalOptionTypeFactory->create();
            if (!empty($typeOptions['id_type'])) {
                $this->_rentalOptionTypeResource->load($modelType, $typeOptions['id_type']);
            }
            $data = [
                'option_title'  => $typeOptions['option'],
                'option_id'     => $modelOption['id'],
                'product_id'    => $modelOption['product_id'],
                'option_number' => $typeOptions['record_id'],
                'price'         => $typeOptions['price'],
            ];

            $modelType->addData($data);
            $this->_rentalOptionTypeResource->save($modelType);
        }
    }

    /**
     * Check Qty on Magento 2.3 MSI
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param $sources
     *
     * @return null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function checkQty($product, $sources)
    {
        $websiteCode = $this->_storeManager->getWebsite(1)->getCode();
        $websiteStockId = $this->getAssignedStockIdForWebsite->execute($websiteCode); //get default website's stock ID

        $qty = null;
        if (!empty($sources['assigned_sources'])) {
            foreach ($sources['assigned_sources'] as $source) { /*get stock qty per valid source*/
                if (isset($source['source_code'])) {
                    $sourceCode = $source['source_code'];
                    if (isset($source['status'])) {
                        if ($this->validateSource($websiteStockId, $sourceCode) && $source['status'] == 1) {
                            $qty += $source['quantity'] ?? '';
                        }
                    }
                }
            }
        }

        $currentQty = $product->getStockData();
        $currentQty = $currentQty['qty'] ?? null;

        if ($product->getStatus() == 1) {
            $currentSalableQty = $this->getSalableQty($product->getSku());
            if (isset($currentQty) && isset($currentSalableQty)) {
                $pendingQty = $currentQty - $currentSalableQty; /*sold but not delivered qty*/
                $qty        = $pendingQty > 0 ? $qty - $pendingQty : $qty; /*get updated salable qty*/
            }
        }

        return $qty;
    }

    /**
     * @param $sku
     * @return float|int
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\InventoryConfigurationApi\Exception\SkuIsNotAssignedToStockException
     */
    public function getSalableQty($sku)
    {
        // get default website's stock Id
        $websiteCode            = $this->_storeManager->getWebsite(1)->getCode();
        $websiteStockId         = $this->getAssignedStockIdForWebsite->execute($websiteCode);
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $websiteStockId);
        $isManageStock          = $stockItemConfiguration->isManageStock();

        //get salable qty of product's valid stock
        return $isManageStock ? $this->getProductSalableQty->execute($sku, $websiteStockId) : 0;
    }

    /**
     * @param $stockId
     * @param $sourceCode
     *
     * @return bool
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateSource($stockId, $sourceCode)
    {
        $sources = $this->getSourcesAPI->execute($stockId);
        foreach ($sources as $source) {
            if ($sourceCode == $source->getSourceCode()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param int $productId
     */
    public function deleteAllOptions($productId)
    {
        $this->_rentalOptionCollection->create()
            ->addFieldToFilter('product_id', ['eq' => $productId])
            ->walk('delete');

        $this->deleteOptionTypes($productId);
    }

    /**
     * @param int $productId
     * @param int|null $optionId
     */
    public function deleteOptionTypes($productId, $optionId = null)
    {
        if (!isset($optionId)) {
            $this->_rentalOptionTypeCollection->create()
                ->addFieldToFilter('product_id', ['eq' => $productId])->walk('delete');
        } else {
            $this->_rentalOptionTypeCollection->create()
                ->addFieldToFilter('product_id', ['eq' => $productId])
                ->addFieldToFilter('option_id', ['eq' => $optionId])->walk('delete');
        }
    }
}
