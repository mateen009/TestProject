<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Observer;

use Magenest\RentalSystem\Model\ResourceModel\RentalOption;
use Magenest\RentalSystem\Model\ResourceModel\RentalOptionType;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magenest\RentalSystem\Model\RentalFactory;
use Magenest\RentalSystem\Model\RentalPriceFactory;
use Magenest\RentalSystem\Model\RentalOptionFactory;
use Magenest\RentalSystem\Model\RentalOptionTypeFactory;
use Magenest\RentalSystem\Helper\Rental;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Cart implements ObserverInterface
{
    /** @var LoggerInterface */
    protected $_logger;

    /** @var RequestInterface */
    protected $_request;

    /** @var ScopeConfigInterface */
    protected $_scopeConfig;

    /** @var ProductRepository */
    protected $_productRepository;

    /** @var RentalOptionFactory */
    protected $_rentalOptionFactory;

    /** @var RentalOption */
    protected $rentalOptionResources;

    /** @var RentalOptionTypeFactory */
    protected $_rentalOptionTypeFactory;

    /** @var RentalOptionType */
    protected $rentalOptionTypeResources;

    /** @var TimezoneInterface */
    protected $_timezone;

    /** @var PriceHelper */
    protected $_price;

    /** @var Json */
    private $json;

    /**
     * Cart constructor.
     *
     * @param LoggerInterface $logger
     * @param RequestInterface $request
     * @param ScopeConfigInterface $scopeConfig
     * @param RentalOptionFactory $rentalOptionFactory
     * @param RentalOptionTypeFactory $rentalOptionTypeFactory
     * @param RentalOption $rentalOptionResources
     * @param RentalOptionType $rentalOptionTypeResources
     * @param ProductRepository $productRepository
     * @param Json $json
     * @param PriceHelper $_price
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        LoggerInterface $logger,
        RequestInterface $request,
        ScopeConfigInterface $scopeConfig,
        RentalOptionFactory $rentalOptionFactory,
        RentalOptionTypeFactory $rentalOptionTypeFactory,
        RentalOption $rentalOptionResources,
        RentalOptionType $rentalOptionTypeResources,
        ProductRepository $productRepository,
        Json $json,
        PriceHelper $_price,
        TimezoneInterface $timezone
    ) {
        $this->json                      = $json;
        $this->_logger                   = $logger;
        $this->_request                  = $request;
        $this->_scopeConfig              = $scopeConfig;
        $this->_productRepository        = $productRepository;
        $this->_rentalOptionFactory      = $rentalOptionFactory;
        $this->rentalOptionResources     = $rentalOptionResources;
        $this->_rentalOptionTypeFactory  = $rentalOptionTypeFactory;
        $this->rentalOptionTypeResources = $rentalOptionTypeResources;
        $this->_timezone                 = $timezone;
        $this->_price                    = $_price;
    }

    /**
     * @param $price
     *
     * @return float|string
     */
    public function getLocatePrice($price)
    {
        return $this->_price->currency($price, true, false);
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $additionalOptions = [];

        try {
            /** @var \Magento\Quote\Model\Quote\Item $item */
            $item = $observer->getEvent()->getQuoteItem();
            /** @var \Magento\Catalog\Model\Product $product */
            $product     = $item->getProduct();
            $productId   = $product->getId();
            $data        = $this->_request->getParams();
            $productType = $this->_productRepository->getById($productId)->getTypeId();
            if ($productType == 'rental') {
                if (!empty($data['additional_options'])) {
                    $options   = $data['additional_options'];
                    $fromStamp = $options['rental_from'];
                    $toStamp   = $options['rental_to'];

                    if (!empty($options['rental_price'])) {
                        $item->setOriginalCustomPrice($options['rental_price']);
                    }

                    if (!empty($options['options'])) {
                        $this->setRentalOptions($additionalOptions, $options['options'], $fromStamp, $toStamp);
                    }

                    if (isset($options['local_pickup'])) {
                        $item->setWeight(null);
                        $item->setIsVirtual(1);
                    }

                    $this->setTimeOptions($additionalOptions, $fromStamp, $toStamp);

                    $item->addOption([
                        'code'  => 'additional_options',
                        'value' => $this->json->serialize($additionalOptions)
                    ]);
                }
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
    }

    /**
     * @param $optionsArray
     * @param $fromStamp
     * @param $toStamp
     */
    private function setTimeOptions(&$optionsArray, $fromStamp, $toStamp)
    {
        $dateFormat = $this->_scopeConfig->getValue(Rental::XML_PATH_DATE_FORMAT)
            . $this->_scopeConfig->getValue(Rental::XML_PATH_TIME_FORMAT);

        $from = $this->getFormattedDate($fromStamp, $dateFormat);
        $to   = $this->getFormattedDate($toStamp, $dateFormat);

        $optionsArray[] = [
            'label' => __("From"),
            'value' => $from
        ];

        $optionsArray[] = [
            'label' => __("To"),
            'value' => $to
        ];
    }

    /**
     * @param $optionsArray
     * @param $rentalOptions
     * @param $fromStamp
     * @param $toStamp
     */
    private function setRentalOptions(&$optionsArray, $rentalOptions, $fromStamp, $toStamp)
    {
        foreach ($rentalOptions as $rentalOption) {
            if (!empty($rentalOption)) {
                $optionData = explode("_", $rentalOption);
                $optionId   = $optionData[2];
                $typeId     = $optionData[1];
                $typePrice  = $optionData[3];
                $price      = $optionData[0];
                $start      = strtotime($this->getFormattedDate($fromStamp, 'yyyy/MM/dd HH:mm'));
                $end        = strtotime($this->getFormattedDate($toStamp, 'yyyy/MM/dd HH:mm'));
                $timeDiff   = ceil((($end - $start) / 60) / 60);

                if ($typePrice == 'perday') {
                    $price = $price * ceil($timeDiff / 24);
                }
                if ($typePrice == 'perhour') {
                    $price = $price * $timeDiff;
                }

                $optionModel = $this->_rentalOptionFactory->create();
                $this->rentalOptionResources->load($optionModel, $optionId);
                $optionTypeModel   = $this->_rentalOptionTypeFactory->create();
                $this->rentalOptionTypeResources->load($optionTypeModel, $typeId);

                if ($optionTitle = $optionModel->getOptionTitle() && $typeTitle = $optionTypeModel->getOptionTitle()) {
                    if ($price > 0) {
                        $typeTitle .= " (" . $this->getLocatePrice($price) . ")";
                    }
                    $optionsArray[] = [
                        'label' => $optionTitle,
                        'value' => $typeTitle
                    ];
                }
            }
        }
    }

    /**
     * @param $timestamp
     * @param $format
     * @return string
     */
    private function getFormattedDate($timestamp, $format)
    {
        return $this->_timezone->formatDateTime(date('Y-m-d H:i', $timestamp), 3, 3, null, null, $format);
    }
}
