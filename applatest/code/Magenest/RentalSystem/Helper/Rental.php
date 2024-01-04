<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Helper;

use Magenest\RentalSystem\Model\RentalOptionFactory;
use Magenest\RentalSystem\Model\RentalOptionTypeFactory;
use Magenest\RentalSystem\Model\RentalRule\Option\RepeatOptions;
use Magenest\RentalSystem\Model\ResourceModel\RentalOption as RentalOptionResource;
use Magenest\RentalSystem\Model\ResourceModel\RentalOptionType as RentalOptionTypeResource;
use Magenest\RentalSystem\Model\ResourceModel\RentalRuleProduct\CollectionFactory as RentalRuleCollection;
use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\MailException;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magenest\RentalSystem\Model\RentalFactory;
use Magenest\RentalSystem\Model\ResourceModel\Rental as RentalResource;
use Magenest\RentalSystem\Model\RentalOrderFactory;
use Magenest\RentalSystem\Model\ResourceModel\RentalOrder as RentalOrderResource;
use Magenest\RentalSystem\Model\ResourceModel\RentalOrder\CollectionFactory as RentalOrderCollection;
use Magenest\RentalSystem\Model\ResourceModel\RentalOption\CollectionFactory as RentalOptionCollection;
use Magenest\RentalSystem\Model\ResourceModel\RentalOptionType\CollectionFactory as RentalOptionTypeCollection;
use Magenest\RentalSystem\Model\RentalPriceFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory as ItemCollectionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magenest\RentalSystem\Model\Config\Source\DeliveryType;
use Magenest\RentalSystem\Model\Status;

class Rental extends AbstractHelper
{
    /**
     * Const Email
     */
    const XML_PATH_EMAIL_SENDER = 'trans_email/ident_general/email';

    /**
     * Const Name
     */
    const XML_PATH_NAME_SENDER = 'trans_email/ident_general/name';

    /**
     * Global max duration
     */
    const XML_PATH_MAX_DURATION = 'rental_system/rental/max_duration';

    const XML_PATH_DATE_FORMAT = 'rental_system/locale/dateformat';
    const XML_PATH_TIME_FORMAT = 'rental_system/locale/timeformat';

    const XML_PATH_QTY_HOLD = 'rental_system/rental/hold';

    const XML_PATH_CODE_PATTERN = 'rental_system/general/pattern_code';

    /** @var RentalFactory */
    protected $_rentalFactory;

    /** @var RentalResource */
    protected $_rentalResource;

    /** @var RentalOrderFactory */
    protected $_rentalOrderFactory;

    /** @var RentalOrderResource */
    protected $_rentalOrderResource;

    /** @var RentalOrderCollection */
    protected $_rentalOrderCollection;

    /** @var RentalOptionFactory */
    protected $rentalOptionFactory;

    /** @var RentalOptionResource */
    protected $rentalOptionResource;

    /** @var RentalOptionCollection */
    protected $_rentalOptionCollection;

    /** @var RentalOptionTypeFactory */
    protected $rentalOptionTypeFactory;

    /** @var RentalOptionTypeResource */
    protected $rentalOptionTypeResource;

    /** @var RentalOptionTypeCollection */
    protected $_rentalOptionTypeCollection;

    /** @var RentalPriceFactory */
    protected $_rentalPriceFactory;

    /** @var TransportBuilder */
    protected $_transportBuilder;

    /** @var StateInterface */
    protected $_inlineTranslation;

    /** @var StoreManagerInterface */
    protected $_storeManager;

    /** @var TimezoneInterface */
    protected $_timezone;

    /** @var ItemCollectionFactory */
    protected $_itemCollectionFactory;

    /** @var RentalRuleCollection */
    protected $rentalRuleCollection;

    /**
     * Rental constructor.
     *
     * @param Context $context
     * @param RentalFactory $rentalFactory
     * @param RentalResource $_rentalResource
     * @param RentalOrderFactory $rentalOrderFactory
     * @param RentalOrderResource $rentalOrderResource
     * @param RentalOrderCollection $rentalOrderCollection
     * @param RentalOptionFactory $rentalOptionFactory
     * @param RentalOptionResource $rentalOptionResource
     * @param RentalOptionCollection $rentalOptionCollection
     * @param RentalOptionTypeFactory $rentalOptionTypeFactory
     * @param RentalOptionTypeResource $rentalOptionTypeResource
     * @param RentalOptionTypeCollection $rentalOptionTypeCollection
     * @param RentalPriceFactory $rentalPriceFactory
     * @param RentalRuleCollection $rentalRuleCollection
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param StoreManagerInterface $storeManager
     * @param ItemCollectionFactory $itemCollectionFactory
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        Context $context,
        RentalFactory $rentalFactory,
        RentalResource $_rentalResource,
        RentalOrderFactory $rentalOrderFactory,
        RentalOrderResource $rentalOrderResource,
        RentalOrderCollection $rentalOrderCollection,
        RentalOptionFactory $rentalOptionFactory,
        RentalOptionResource $rentalOptionResource,
        RentalOptionCollection $rentalOptionCollection,
        RentalOptionTypeFactory $rentalOptionTypeFactory,
        RentalOptionTypeResource $rentalOptionTypeResource,
        RentalOptionTypeCollection $rentalOptionTypeCollection,
        RentalPriceFactory $rentalPriceFactory,
        RentalRuleCollection $rentalRuleCollection,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        StoreManagerInterface $storeManager,
        ItemCollectionFactory $itemCollectionFactory,
        TimezoneInterface $timezone
    ) {
        parent::__construct($context);
        $this->_rentalFactory              = $rentalFactory;
        $this->_rentalResource             = $_rentalResource;
        $this->_rentalOrderFactory         = $rentalOrderFactory;
        $this->_rentalOrderResource        = $rentalOrderResource;
        $this->_rentalOrderCollection      = $rentalOrderCollection;
        $this->rentalOptionFactory         = $rentalOptionFactory;
        $this->rentalOptionResource        = $rentalOptionResource;
        $this->_rentalOptionCollection     = $rentalOptionCollection;
        $this->rentalOptionTypeFactory     = $rentalOptionTypeFactory;
        $this->rentalOptionTypeResource    = $rentalOptionTypeResource;
        $this->_rentalOptionTypeCollection = $rentalOptionTypeCollection;
        $this->_rentalPriceFactory         = $rentalPriceFactory;
        $this->rentalRuleCollection        = $rentalRuleCollection;
        $this->_transportBuilder           = $transportBuilder;
        $this->_inlineTranslation          = $inlineTranslation;
        $this->_storeManager               = $storeManager;
        $this->_timezone                   = $timezone;
        $this->_itemCollectionFactory      = $itemCollectionFactory;
    }

    /**
     * @param $productId
     *
     * @return bool
     */
    public function isRental($productId)
    {
        $rentalId = $this->_rentalFactory->create()->loadByProductId($productId)->getId();

        return !empty($rentalId);
    }

    /**
     * @param $id
     * @param $hours
     *
     * @return bool
     */
    public function validateDuration($id, $hours)
    {
        $rentalData  = $this->_rentalFactory->create()->loadByProductId($id)->getData();
        $maxDuration = $rentalData['max_duration'] ?? $this->scopeConfig->getValue(self::XML_PATH_MAX_DURATION);
        $maxDuration = (int)$maxDuration * 24;

        return $maxDuration == 0 || $hours <= $maxDuration;
    }

    /**
     * @param $id
     * @param $additionalOptions
     *
     * @return bool
     */
    public function validatePrice($id, $additionalOptions)
    {
        $priceData = $this->_rentalPriceFactory->create()->loadByProductId($id)->getData();
        $basePrice = $priceData['base_price'];
        $baseHour  = $this->getDuration($priceData['base_period']);
        $addPrice  = $priceData['additional_price'] ?? 0;
        $addHour   = isset($priceData['additional_period']) ? $this->getDuration($priceData['additional_period']) : 0;

        $requestHours = $additionalOptions['rental_hours'];
        $requestPrice = $additionalOptions['rental_price'];
        if ($addPrice > 0 && $addHour > 0) {
            if ($requestHours <= $baseHour) {
                $price = $basePrice;
            } else {
                $price = $basePrice + ceil(($requestHours - $baseHour) / $addHour) * $addPrice;
            }
        } else {
            $price = ceil($requestHours / $baseHour) * $basePrice;
        }

        if (!empty($additionalOptions['options'])) {
            $options = $additionalOptions['options'];
            foreach ($options as $key => $value) {
                if (!empty($value)) {
                    $optionData = explode('_', $value);
                    $optionId   = $optionData[1];
                    $price      += $this->getOptionCost($requestHours, $optionId);
                }
            }
        }

        $price = $this->applyRule(
            $id,
            $price,
            $basePrice,
            $requestHours,
            $additionalOptions['rental_from_utc'],
            $additionalOptions['rental_to_utc']
        );
        return ($price == $requestPrice);
    }

    /**
     * @param $productId
     * @param $currentPrice
     * @param $basePrice
     * @param $duration
     * @param $rentFrom
     * @param $rentTo
     * @return float|int
     */
    private function applyRule($productId, $currentPrice, $basePrice, $duration, $rentFrom, $rentTo)
    {
        $finalPrice = $currentPrice;
        $applicableRules = $this->rentalRuleCollection->create()->setJoin(true)
            ->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('to_date', ['gteq' => date_create()->format('Y-m-d')])
            ->setOrder('sort_order', Collection::SORT_ORDER_DESC)
            ->getItems();
        $finalRules = [];
        foreach ($applicableRules as $rule) {
            $simpleAction = $rule->getSimpleAction();
            $fromDate = date_create_from_format("Y-m-d", $rule->getFromDate())
                ->setTime(0, 0)
                ->getTimestamp();
            $toDate = date_create_from_format("Y-m-d", $rule->getToDate())
                ->setTime(0, 0)
                ->getTimestamp();
            if (($rentFrom < $fromDate && $rentTo < $fromDate) || ($rentFrom > $toDate && $rentTo > $toDate)) {
                continue;
            }
            if ($simpleAction === "to_percent" || $simpleAction === "to_fixed") {
                $finalRules[] = $rule;
                continue;
            }
            if ($fromDate > $rentFrom) {
                $duration -= ($fromDate - $rentFrom) / 3600;
            }
            if ($rentTo > $toDate) {
                $duration -= ($rentTo - $toDate) / 3600;
            }
            switch ($rule->getRepeatType()) {
                case RepeatOptions::OPTION_BY_DAYS:
                    $period = intdiv($duration, $rule->getRepeatPeriod() * 24);
                    break;
                case RepeatOptions::OPTION_BY_MONTHS:
                    $period = intdiv($duration, $rule->getRepeatPeriod() * 24 * 30);
                    break;
                default:
                    $period = intdiv($duration, $rule->getRepeatPeriod());
                    break;
            }
            if ($period < 0) {
                continue;
            }
            if ($simpleAction === "by_percent") {
                $finalPrice = $finalPrice - ($basePrice * $rule->getDiscountAmount() / 100) * $period;
            } else {
                $finalPrice = $finalPrice - $rule->getDiscountAmount() * $period;
            }
        }
        foreach ($finalRules as $finalRule) {
            if ($finalRule->getSimpleAction() === "to_percent") {
                $finalPrice = $finalPrice * (100 - $finalRule->getDiscountAmount()) / 100;
            } else {
                $finalPrice = $finalPrice - $finalRule->getDiscountAmount();
            }
        }

        return $finalPrice > 0 ? round($finalPrice, 2) : 0;
    }

    /**
     * @param $hours
     * @param $id
     *
     * @return float|int
     */
    protected function getOptionCost($hours, $id)
    {
        $optionTypeData = $this->_rentalOptionTypeCollection->create()->addFieldToFilter('id', $id)->getFirstItem();
        $typePrice      = $optionTypeData['price'];
        $optionId       = $optionTypeData['option_id'];
        $optionData     = $this->_rentalOptionCollection->create()->addFieldToFilter('id', $optionId)->getFirstItem();
        $optionsType    = $optionData['type'];

        switch ($optionsType) {
            case 'fixed':
                return $typePrice;
            case 'per_day':
                return ceil($hours / 24) * $typePrice;
            case 'per_hour':
                return $hours * $typePrice;
        }

        return 0;
    }

    /**
     * @param string $period
     *
     * @return string
     */
    public function getDuration($period)
    {
        $length   = strlen($period);
        $duration = (int)substr($period, 0, $length - 1);
        $type     = substr($period, -1);

        if ($type == 'w' || $type == 'W') {
            return $duration * 168;
        } elseif ($type == 'D' || $type == 'd') {
            return $duration * 24;
        } else {
            return $duration;
        }
    }

    /**
     * @param string $period
     * @return array
     */
    public function getPeriodStr($period)
    {
        $length   = strlen($period);
        $duration = (int)substr($period, 0, $length - 1);
        $type     = substr($period, -1);
        if ($duration > 1) {
            if ($type == 'w' || $type == 'W') {
                $typeStr = 'weeks';
            } elseif ($type == 'd' || $type == 'D') {
                $typeStr = 'days';
            } else {
                $typeStr = 'hours';
            }
        } else {
            if ($type == 'w' || $type == 'W') {
                $typeStr = 'week';
            } elseif ($type == 'd' || $type == 'D') {
                $typeStr = 'day';
            } else {
                $typeStr = 'hour';
            }
        }

        return [$duration, $typeStr];
    }

    /**
     * Get Qty invoiced
     *
     * @param $rentalId
     * @param $from
     * @param $to
     *
     * @return int
     */
    public function sendingQty($rentalId, $from, $to)
    {
        $invoicedTotal = 0;
        $rents         = $this->_rentalOrderCollection->create()->addFieldToFilter('rental_id', $rentalId)
            ->addFieldToSelect(['qty_invoiced'])
            ->addFieldToSelect('order_item_id')
            ->addFieldToFilter(
                'status',
                ['in', [Status::PENDING, Status::DELIVERING, Status::DELIVERED, Status::RETURNING]]
            );

        $rents->getSelect()->where(
            "(start_time <= '$from' AND end_time >= '$from')"
            . " OR (start_time >= '$from' AND start_time <= '$to')"
        );

        foreach ($rents as $rent) {
            $order_item_id  = $rent->getData('order_item_id');
            $joinConditions = 'main_table.item_id = s.order_item_id';
            $collection     = $this->_itemCollectionFactory->create()
                ->addFieldToFilter('item_id', $order_item_id);
            $collection->join(
                ['s' => $collection->getTable('magenest_rental_order')],
                $joinConditions
            );

            //get qty refunded after credit memo
            if ($collection->getData()) {
                $qty_refunded = $collection->getData()[0]['qty_refunded'];
            } else {
                $qty_refunded = 0;
            }
            $invoiced = $rent->getData('qty_invoiced') - $qty_refunded;

            $invoicedTotal += $invoiced;
        }

        return $invoicedTotal;
    }

    /**
     * Get Qty Unpaid
     *
     * @param $rentalId
     * @param $from
     * @param $to
     * @param $holdQty
     *
     * @return int|string
     */
    public function unpaidQty($rentalId, $from, $to, $holdQty)
    {
        $unpaidTotal = 0;
        $rents       = $this->_rentalOrderCollection->create()->addFieldToFilter('rental_id', $rentalId)
            ->addFieldToSelect(['qty', 'qty_invoiced'])
            ->addFieldToFilter(
                'status',
                ['in', [Status::UNPAID, Status::PENDING, Status::DELIVERING, Status::DELIVERED, Status::RETURNING]]
            );

        $rents->getSelect()->where(
            "(start_time <= '$from' AND end_time >= '$from') OR (start_time >= '$from' AND start_time <= '$to')"
        );
        foreach ($rents->getData() as $rent) {
            $unpaidTotal += $rent['qty'] - $rent['qty_invoiced'];
        }

        return min($unpaidTotal, $holdQty);
    }

    /**
     * Get qty Available
     *
     * @param $productId
     * @param $from
     * @param $to
     *
     * @return int|mixed|string
     */
    public function getAvailableQty($productId, $from, $to)
    {
        $model        = $this->_rentalFactory->create()->loadByProductId($productId);
        $holdQty      = $model->getData('hold') ?? $this->scopeConfig->getValue(self::XML_PATH_QTY_HOLD);
        $initialQty   = $model->getData('initial_qty');
        $unpaidQty    = $this->unpaidQty($model->getId(), $from, $to, $holdQty);
        $sendingQty   = $this->sendingQty($model->getId(), $from, $to);

        return $initialQty - $sendingQty - $unpaidQty;
    }

    /**
     * @param array $options
     *
     * @return string $data
     */
    public function decodeOptions($options)
    {
        $data = '';

        foreach ($options as $option) {
            if ($option) {
                $optionData = explode("_", $option);
                $optionId   = $optionData[2];
                $typeId     = $optionData[1];

                $optionTitle = $this->_rentalOptionCollection->create()
                    ->addFieldToFilter('id', $optionId)
                    ->addFieldToSelect('option_title')
                    ->getFirstItem()->getData('option_title');
                $typeTitle   = $this->_rentalOptionTypeCollection->create()
                    ->addFieldToFilter('id', $typeId)
                    ->addFieldToSelect('option_title')
                    ->getFirstItem()->getData('option_title');

                if (!empty($optionTitle) && !empty($typeTitle)) {
                    $data = $data . $optionTitle . ': ' . $typeTitle . '. ';
                }
            }
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getDateFormat()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_DATE_FORMAT)
            . $this->scopeConfig->getValue(self::XML_PATH_TIME_FORMAT);
    }

    /**
     * @param $data
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendReceipt($data)
    {
        try {
            if ($data['type'] == DeliveryType::SHIPPING) {
                $type = $data['type'];
            } elseif ($data['type'] == DeliveryType::PICKUP) {
                $type = $data['type'];
            } elseif ($data['type'] == DeliveryType::BOTH) {
                if (strlen(strstr($data['delivery_value'], 'Address')) > 0) {
                    $type = DeliveryType::PICKUP;
                } else {
                    $type = DeliveryType::SHIPPING;
                }
            } else {
                $type = DeliveryType::SHIPPING;
            }
//            $dateFormat = $this->getDateFormat();
            $rentalId = $data['rental_id'];
            $this->_inlineTranslation->suspend();
            $emailTemplate = $this->_rentalFactory->create()->getEmailTemplate($rentalId);
            $startTime = $this->_timezone->formatDateTime($data['start_time'], 3, 3, null, null, 'yyyy/MM/dd HH:mm');
            $endTime = $this->_timezone->formatDateTime($data['end_time'], 3, 3, null, null, 'yyyy/MM/dd HH:mm');

            $this->_transportBuilder->setTemplateIdentifier($emailTemplate)->setTemplateOptions(
                [
                    'area'  => Area::AREA_FRONTEND,
                    'store' => $this->_storeManager->getStore()->getId(),
                ]
            )->setTemplateVars(
                [
                    'store'              => $this->_storeManager->getStore(),
                    'store URL'          => $this->_storeManager->getStore()->getBaseUrl(),
                    'title'              => $data['title'],
                    'customer_name'      => $data['customer_name'],
                    'rental_code'        => $data['code'],
                    'order_id'           => $data['order_increment_id'],
                    'start_time'         => $startTime,
                    'end_time'           => $endTime,
                    'qty'                => $data['qty'],
                    'qty_invoiced'       => $data['qty_invoiced'],
                    'rent_type'          => $type == DeliveryType::SHIPPING ? __('Shipping') : __('Local Pickup'),
                    'additional_options' => $data['information']
                ]
            )->setFromByScope(
                [
                    'email' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER),
                    'name'  => $this->scopeConfig->getValue(self::XML_PATH_NAME_SENDER)
                ]
            )->addTo(
                $data['customer_email'],
                $data['customer_name']
            );

            $this->_transportBuilder->getTransport()->sendMessage();
            $this->_inlineTranslation->resume();

            return;

        } catch (MailException $e) {
            $this->_logger->critical($e->getMessage());
        }
    }

    /**
     * Generate code
     * @return mixed|string|string[]|null
     */
    public function generateCode()
    {
        $gen_arr = [];
        $pattern = $this->scopeConfig->getValue(self::XML_PATH_CODE_PATTERN);
        if (!$pattern) {
            $pattern = '[A2][N1][A2]Magenest[N1][A1]';
        }

        preg_match_all("/\[[AN][.*\d]*\]/", $pattern, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $delegate = substr($match [0], 1, 1);
            $length   = substr($match [0], 2, strlen($match [0]) - 3);
            $gen      = '';
            if ($delegate == 'A') {
                $gen = $this->generateString($length);
            } elseif ($delegate == 'N') {
                $gen = $this->generateNum($length);
            }

            $gen_arr [] = $gen;
        }
        foreach ($gen_arr as $g) {
            $pattern = preg_replace('/\[[AN][.*\d]*\]/', $g, $pattern, 1);
        }

        return $pattern;
    }

    /**
     * Generate String
     *
     * @param $length
     *
     * @return string
     */
    protected function generateString($length)
    {
        if ($length == 0 || $length == null || $length == '') {
            $length = 5;
        }
        $string = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        $rand   = '';
        for ($i = 0; $i < $length; $i++) {
            $rand .= $string[rand(0, 51)];
        }

        return $rand;
    }

    /**
     * Generate Number
     *
     * @param $length
     *
     * @return string
     */
    protected function generateNum($length)
    {
        if ($length == 0 || $length == null || $length == '') {
            $length = 5;
        }
        $number = "0123456789";
        $rand   = '';
        for ($i = 0; $i < $length; $i++) {
            $rand .= $number[rand(0, 9)];
        }

        return $rand;
    }

    /**
     * Get address
     *
     * @param \Magento\Sales\Api\Data\OrderAddressInterface $address
     *
     * @return string
     */
    public function getAddressStr($address)
    {
        $streetArr = $address->getStreet();
        $line      = '';
        foreach ($streetArr as $street) {
            $line = $line . $street . ' ';
        }

        return $line . ' ' . $address->getRegion() . ' ' . $address->getCity() . ', ' . $address->getPostcode();
    }

    /**
     * @param $option
     * @param $price
     * @return array
     */
    public function getOptionTitleAndPrice($option, $price)
    {
        $optionTypeId     = $option[1];
        $rentalOptionType = $this->rentalOptionTypeFactory->create();
        $this->rentalOptionTypeResource->load($rentalOptionType, $optionTypeId);
        $rentalOptionTypeTitle = $rentalOptionType->getOptionTitle();

        $optionId     = $option[2];
        $rentalOption = $this->rentalOptionFactory->create();
        $this->rentalOptionResource->load($rentalOption, $optionId);
        $rentalOptionTitle = $rentalOption->getOptionTitle();

        $type = $option[3];

        return [
            'price'             => $price,
            'option_title'      => $rentalOptionTitle,
            'option_type_title' => $rentalOptionTypeTitle,
            'type'              => $type
        ];
    }

    /**
     * @param $time
     * @param $separator
     * @return string
     */
    public function getDateString($time, $separator)
    {
        $dateFormat = $this->scopeConfig->getValue(self::XML_PATH_DATE_FORMAT);
        $timeFormat = $this->scopeConfig->getValue(self::XML_PATH_TIME_FORMAT);
        $format     = $dateFormat . $separator . $timeFormat;

        return $this->_timezone->formatDateTime($time, 3, 3, null, null, $format);
    }
}
