<?php
/**
 * Copyright © 2020 Magenest. All rights reserved.
 */

namespace Magenest\RentalSystem\Model\Export;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;
use Magenest\RentalSystem\Model\Import\Product\Type\Rental as ImportModel;
use Magenest\RentalSystem\Helper\ImportExport as ExportHelper;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class RowCustomizer implements RowCustomizerInterface
{
    const TYPE = 'rental';

    /**
     * @var array
     */
    private $rentalData = [];

    /**
     * @var string[]
     */
    private $rentalColumns = [
        ImportModel::EMAIL_TEMPLATE,
        ImportModel::COL_DELIVERY_TYPE,
        ImportModel::LEAD_TIME,
        ImportModel::MAX_DURATION,
        ImportModel::PICKUP_ADDRESS,
        ImportModel::HOLD,
        ImportModel::RENTAL_PRICE_BASE,
        ImportModel::RENTAL_PRICE_BASE_PERIOD,
        ImportModel::RENTAL_PRICE_ADDITIONAL_PRICE,
        ImportModel::RENTAL_PRICE_ADDITIONAL_PERIOD,
        ImportModel::OPTIONS,
    ];

    /** @var ExportHelper */
    private $helper;

    /** @var StoreManagerInterface */
    private $storeManager;

    /**
     * RowCustomizer constructor.
     *
     * @param ExportHelper $helper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ExportHelper $helper,
        StoreManagerInterface $storeManager
    ) {
        $this->helper       = $helper;
        $this->storeManager = $storeManager;
    }

    /**
     * @param mixed $collection
     * @param int[] $productIds
     * @return void
     */
    public function prepareData($collection, $productIds)
    {
        $productCollection = clone $collection;
        $productCollection->addAttributeToFilter('entity_id', ['in' => $productIds])
            ->addAttributeToFilter('type_id', ['eq' => self::TYPE]);
        // set global scope during export
        $this->storeManager->setCurrentStore(Store::DEFAULT_STORE_ID);

        while ($product = $productCollection->fetchItem()) {
            /** @var $product ProductInterface */
            $productId         = $product->getId();
            $baseRentalData    = $this->helper->getBaseRentalInformation($productId);
            $rentalPriceData   = $this->helper->getRentalPriceInformation($productId);
            $rentalOptionsData = $this->helper->getRentalOptionsInformation($productId);

            $data                       = array_merge($baseRentalData, $rentalPriceData);
            $data[ImportModel::OPTIONS] = $rentalOptionsData;

            $this->rentalData[$productId] = $data;
        }
    }

    /**
     * @param array $columns
     * @return array|string[]
     */
    public function addHeaderColumns($columns)
    {
        return array_merge($columns, $this->rentalColumns);
    }

    /**
     * @param array $dataRow
     * @param int $productId
     * @return array
     */
    public function addData($dataRow, $productId): array
    {
        if (!empty($this->rentalData[$productId])) {
            $dataRow = array_merge($dataRow, $this->rentalData[$productId]);
        }

        return $dataRow;
    }

    /**
     * @param array $additionalRowsCount
     * @param int $productId
     * @return array
     */
    public function getAdditionalRowsCount($additionalRowsCount, $productId): array
    {
        if (!empty($this->rentalData[$productId])) {
            $additionalRowsCount = max($additionalRowsCount, count($this->rentalData[$productId]));
        }

        return $additionalRowsCount;
    }
}
