<?php
/**
 * Copyright © 2020 Magenest. All rights reserved.
 */

namespace Magenest\RentalSystem\Helper;

use Magenest\RentalSystem\Model\Import\Product\Type\Rental;
use Magenest\RentalSystem\Model\RentalFactory;
use Magenest\RentalSystem\Model\RentalOption;
use Magenest\RentalSystem\Model\RentalOptionType;
use Magenest\RentalSystem\Model\RentalPriceFactory;
use Magenest\RentalSystem\Model\ResourceModel\RentalOption\CollectionFactory as RentalOptionCollection;
use Magenest\RentalSystem\Model\ResourceModel\RentalOptionType\CollectionFactory as RentalOptionTypeCollection;
use Magenest\RentalSystem\Model\Import\Product\Type\Rental as ImportModel;

class ImportExport
{
    /** @var RentalFactory */
    protected $rental;

    /** @var RentalPriceFactory */
    protected $rentalPrice;

    /** @var RentalOptionCollection */
    protected $rentalOptionCollection;

    /** @var RentalOptionTypeCollection */
    protected $rentalOptionTypeCollection;

    /**
     * ImportExport constructor.
     *
     * @param RentalFactory $rental
     * @param RentalPriceFactory $rentalPrice
     * @param RentalOptionCollection $rentalOptionCollection
     * @param RentalOptionTypeCollection $rentalOptionTypeCollection
     */
    public function __construct(
        RentalFactory $rental,
        RentalPriceFactory $rentalPrice,
        RentalOptionCollection $rentalOptionCollection,
        RentalOptionTypeCollection $rentalOptionTypeCollection
    ) {
        $this->rental                     = $rental;
        $this->rentalPrice                = $rentalPrice;
        $this->rentalOptionCollection     = $rentalOptionCollection;
        $this->rentalOptionTypeCollection = $rentalOptionTypeCollection;
    }

    /**
     * @param $productId
     *
     * @return array
     */
    public function getBaseRentalInformation($productId)
    {
        $data = $this->rental->create()->loadByProductId($productId);

        return [
            ImportModel::EMAIL_TEMPLATE    => $data->getData('email_template'),
            ImportModel::COL_DELIVERY_TYPE => $data->getData('type'),
            ImportModel::LEAD_TIME         => $data->getLeadTime(),
            ImportModel::MAX_DURATION      => $data->getMaxDuration(),
            ImportModel::PICKUP_ADDRESS    => $data->getPickupAddress(),
            ImportModel::HOLD              => $data->getHold()
        ];
    }

    /**
     * @param $productId
     *
     * @return array
     */
    public function getRentalPriceInformation($productId)
    {
        $data = $this->rentalPrice->create()->loadByProductId($productId);

        return [
            ImportModel::RENTAL_PRICE_BASE              => $data->getBasePrice(),
            ImportModel::RENTAL_PRICE_BASE_PERIOD       => $data->getBasePeriod(),
            ImportModel::RENTAL_PRICE_ADDITIONAL_PRICE  => $data->getAdditionalPrice() ?? 0,
            ImportModel::RENTAL_PRICE_ADDITIONAL_PERIOD => $data->getAdditionalPeriod() ?? null
        ];
    }

    public function getRentalOptionsInformation($productId)
    {
        $data = [];

        $options = $this->rentalOptionCollection->create()->addFieldToFilter('product_id', $productId)->getItems();
        /** @var RentalOption $option */
        foreach ($options as $option) {
            $optionId   = $option->getId();
            $title      = $option->getOptionTitle();
            $priceType  = $option->getType();
            $isRequired = $option->getIsRequired();
            $types      = $this->rentalOptionTypeCollection->create()
                ->addFieldToFilter('option_id', $optionId)
                ->getItems();
            /** @var RentalOptionType $type */
            foreach ($types as $type) {
                $typeTitle = $type->getOptionTitle();
                $price     = $type->getPrice();
                $data[]    = implode(",", [$title, $priceType, $isRequired, $typeTitle, $price]);
            }
        }

        return implode(ImportModel::OPTIONS_SEPARATOR, $data);
    }

    /**
     * Check if 'rental' field is correct
     *
     * @param $rowData
     *
     * @return bool
     */
    public function isRowRentalCorrectFormat($rowData)
    {
        //empty attribute value
        if (!is_array($rowData)) {
            return false;
        }
        foreach ($rowData as $key => $value) {
            //attribute value not numeric
            if (in_array($key, Rental::RENTAL_ATTRIBUTES_NUMERIC) && !empty($value) && !is_numeric($value)) {
                return false;
            }
        }

        return true;
    }
}
