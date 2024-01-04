<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Pricing\Render;

use Magenest\RentalSystem\Helper\Rental;
use Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface;
use Magento\Catalog\Pricing\Price\MinimalPriceCalculatorInterface;
use Magento\Catalog\Pricing\Render\FinalPriceBox as BaseFinalPriceBox;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\View\Element\Template\Context;
use Magenest\RentalSystem\Model\ResourceModel\RentalPrice\CollectionFactory;

class FinalPriceBox extends BaseFinalPriceBox
{
    /** @var CollectionFactory */
    protected $_rentalPriceFactory;

    /** @var Rental */
    protected $rentalHelper;

    /** @var array */
    protected $_rentalData;

    /**
     * FinalPriceBox constructor.
     *
     * @param Context $context
     * @param Rental $rentalHelper
     * @param SaleableInterface $saleableItem
     * @param PriceInterface $price
     * @param RendererPool $rendererPool
     * @param CollectionFactory $rentalPriceFactory
     * @param array $data
     * @param SalableResolverInterface|null $salableResolver
     * @param MinimalPriceCalculatorInterface|null $minimalPriceCalculator
     */
    public function __construct(
        Context $context,
        Rental $rentalHelper,
        SaleableInterface $saleableItem,
        PriceInterface $price,
        RendererPool $rendererPool,
        CollectionFactory $rentalPriceFactory,
        array $data = [],
        SalableResolverInterface $salableResolver = null,
        MinimalPriceCalculatorInterface $minimalPriceCalculator = null
    ) {
        parent::__construct(
            $context,
            $saleableItem,
            $price,
            $rendererPool,
            $data,
            $salableResolver,
            $minimalPriceCalculator
        );
        $this->rentalHelper = $rentalHelper;
        $this->_rentalPriceFactory = $rentalPriceFactory;
    }

    /**
     * @return array
     */
    public function getRentalPrice()
    {
        if (empty($this->_rentalData)) {
            $productId         = $this->getSaleableItem()->getId();
            $this->_rentalData = $this->_rentalPriceFactory->create()
                ->addFieldToFilter('product_id', $productId)
                ->getLastItem()->getData();
        }

        return $this->_rentalData;
    }

    /**
     * @return bool
     */
    public function isPDP()
    {
        return ($this->getZone() == 'item_view');
    }

    /**
     * @return string
     */
    public function getInitialPeriod()
    {
        if (isset($this->getRentalPrice()['base_period'])) {
            $initialString = $this->getPeriodStr($this->getRentalPrice()['base_period']);

            return $initialString[0] . ' ' . __($initialString[1]);
        }

        return '';
    }

    /**
     * @return bool
     */
    public function hasAdditionalPeriod()
    {
        $price = $this->getRentalPrice();
        return isset($price['additional_price']) && isset($price['additional_period']);
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getAdditionalPeriod()
    {
        $additionalString = $this->getPeriodStr($this->getRentalPrice()['additional_period']);

        return __('/extra %1 %2', $additionalString[0], __($additionalString[1]));
    }

    /**
     * @param string $period
     *
     * @return array
     */
    public function getPeriodStr($period)
    {
        return $this->rentalHelper->getPeriodStr($period);
    }
}
