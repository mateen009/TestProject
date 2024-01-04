<?php
/**
 * Copyright © 2020 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Plugin;

use Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition\IsProductSalableForRequestedQtyConditionChain as Subject;
use Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterfaceFactory;

/**
 * Class DisableValidateProductType
 * @package Magenest\RentalSystem\Plugin
 */
class DisableValidateProductType
{
    /**
     * @var GetProductTypesBySkusInterface
     */
    protected $getProductTypesBySkusInterface;

    /**
     * @var ProductSalableResultInterfaceFactory
     */
    protected $productSalableResultInterfaceFactory;

    /**
     * DisableValidateProductType constructor.
     *
     * @param GetProductTypesBySkusInterface $getProductTypesBySkusInterface
     * @param ProductSalableResultInterfaceFactory $productSalableResultInterfaceFactory
     */
    public function __construct(
        GetProductTypesBySkusInterface $getProductTypesBySkusInterface,
        ProductSalableResultInterfaceFactory $productSalableResultInterfaceFactory
    ) {
        $this->getProductTypesBySkusInterface       = $getProductTypesBySkusInterface;
        $this->productSalableResultInterfaceFactory = $productSalableResultInterfaceFactory;
    }

    /**
     * @param Subject $subject
     * @param \Closure $proceed
     * @param string $sku
     * @param int $stockId
     * @param float $requestedQty
     *
     * @return \Magento\InventorySalesApi\Api\Data\ProductSalableResultInterface|mixed
     */
    public function aroundExecute(
        Subject $subject,
        \Closure $proceed,
        string $sku,
        int $stockId,
        float $requestedQty
    ) {
        $productType = $this->getProductTypesBySkusInterface->execute([$sku])[$sku];

        if ($productType == 'rental') {
            return $this->productSalableResultInterfaceFactory->create(['errors' => []]);
        }

        return $proceed($sku, $stockId, $requestedQty);
    }
}