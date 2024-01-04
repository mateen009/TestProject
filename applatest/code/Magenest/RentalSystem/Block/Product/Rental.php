<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Block\Product;

use Magenest\RentalSystem\Model\RentalFactory;
use Magenest\RentalSystem\Model\RentalPriceFactory;
use Magenest\RentalSystem\Model\ResourceModel\RentalOption\CollectionFactory as RentalOptionCollection;
use Magenest\RentalSystem\Model\ResourceModel\RentalOptionType\CollectionFactory as RentalOptionTypeCollection;
use Magenest\RentalSystem\Model\RentalRule\Option\RepeatOptions;
use Magenest\RentalSystem\Model\ResourceModel\RentalRuleProduct\CollectionFactory as RentalRuleCollection;
use Magento\Catalog\Block\Product\Context;
use Magento\Framework\Data\Collection;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Api\TaxCalculationInterface;

class Rental extends Template
{
    /**
     * Google Map API key
     */
    const XML_PATH_GOOGLE_MAP_API_KEY = 'rental_system/general/google_api_key';

    /**
     * Universal maximum rent duration
     */
    const XML_PATH_MAX_DURATION = 'rental_system/rental/max_duration';

    /**
     * Maximum advance period
     */
    const XML_PATH_MAX_ADVANCE = 'rental_system/rental/max_advance_duration';

    /**
     * Days off
     */
    const XML_PATH_DAYS_OFF = 'rental_system/rental/days_off';
    /**
     * Holidays
     */
    const XML_PATH_HOLIDAYS = 'rental_system/rental/holidays';
    /**
     * Work hours
     */
    const XML_PATH_WORK_HOURS = 'rental_system/rental/work_hours';

    /**
     * Locale settings
     */
    const XML_PATH_DAYS_LABEL   = 'rental_system/locale/daysOfWeek';
    const XML_PATH_MONTHS_LABEL = 'rental_system/month/m';
    const XML_PATH_SELECT_TEXT  = 'rental_system/locale/applyLabel';
    const XML_PATH_CLEAR_TEXT   = 'rental_system/locale/cancelLabel';
    const XML_PATH_DATE_FORMAT  = 'rental_system/locale/dateformat';
    const XML_PATH_TIME_FORMAT  = 'rental_system/locale/timeformat';
    const XML_PATH_FIRST_DAY    = 'rental_system/locale/firstDay';

    /**
     * Rental Policy display
     */
    const XML_PATH_POLICY_TEXT         = 'rental_system/policy/policy';
    const XML_PATH_POLICY_REQUIRED     = 'rental_system/policy/required';
    const XML_PATH_POLICY_CONFIRMATION = 'rental_system/policy/confirmation';
    const XML_PATH_POLICY_ERROR        = 'rental_system/policy/errormsg';

    /**
     * Is tax included in PDP display prices
     */
    const XML_PATH_SHOW_TAX_INCLUDE      = 'tax/display/type';
    const XML_PATH_BACKEND_CATALOG_PRICE = 'tax/calculation/price_includes_tax';

    /**
     * @var string
     */
    protected $_template = 'catalog/product/rental.phtml';

    /** @var RentalFactory */
    protected $_rentalFactory;

    /** @var RentalPriceFactory */
    protected $_rentalPriceFactory;

    /** @var RentalOptionCollection */
    protected $rentalOptionCollection;

    /** @var RentalOptionTypeCollection */
    protected $rentalOptionTypeCollection;

    /** @var \Magento\Framework\Registry */
    protected $_coreRegistry;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $_currency;

    /** @var \Magenest\RentalSystem\Model\Rental */
    protected $_rental;

    /** @var FormatInterface */
    protected $_localeFormat;

    /** @var PriceHelper */
    protected $_price;

    /** @var TaxCalculationInterface */
    protected $_taxCalculation;

    /** @var string */
    protected $_editOptions;

    /** @var RentalRuleCollection */
    private $rentalRuleCollection;

    /** @var Json */
    private $json;

    /** @var \Magenest\RentalSystem\Model\ResourceModel\RentalRuleProduct\Collection */
    private $_rentalRule;

    /** @var \Magenest\RentalSystem\Helper\Rental */
    private $rentalHelper;

    /**
     * Rental constructor.
     *
     * @param Json $json
     * @param Context $context
     * @param PriceHelper $_price
     * @param FormatInterface $_localeFormat
     * @param RentalFactory $rentalFactory
     * @param RentalPriceFactory $rentalPriceFactory
     * @param RentalOptionCollection $rentalOptionCollection
     * @param RentalOptionTypeCollection $rentalOptionTypeCollection
     * @param \Magenest\RentalSystem\Helper\Rental $rentalHelper
     * @param RentalRuleCollection $rentalRuleCollection
     * @param TaxCalculationInterface $taxCalculation
     * @param array $data
     */
    public function __construct(
        Json $json,
        Context $context,
        PriceHelper $_price,
        FormatInterface $_localeFormat,
        RentalFactory $rentalFactory,
        RentalPriceFactory $rentalPriceFactory,
        RentalOptionCollection $rentalOptionCollection,
        RentalOptionTypeCollection $rentalOptionTypeCollection,
        \Magenest\RentalSystem\Helper\Rental $rentalHelper,
        RentalRuleCollection $rentalRuleCollection,
        TaxCalculationInterface $taxCalculation,
        array $data = []
    ) {
        $this->_rentalFactory             = $rentalFactory;
        $this->_rentalPriceFactory        = $rentalPriceFactory;
        $this->rentalOptionCollection     = $rentalOptionCollection;
        $this->rentalOptionTypeCollection = $rentalOptionTypeCollection;
        $this->_coreRegistry              = $context->getRegistry();
        $this->_localeFormat              = $_localeFormat;
        $this->_price                     = $_price;
        $this->_taxCalculation            = $taxCalculation;
        $this->rentalRuleCollection       = $rentalRuleCollection;
        $this->rentalHelper               = $rentalHelper;
        $this->json                       = $json;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getCurrentProductId()
    {
        return $this->_coreRegistry->registry('current_product')->getId();
    }

    /**
     * Initiate rental model
     */
    public function initRental()
    {
        $productId = $this->getCurrentProductId();
        $this->_rental = $this->_rentalFactory->create()->loadByProductId($productId);
        $this->_rentalRule = $this->rentalRuleCollection->create()->setJoin(true)
            ->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('to_date', ['gteq' => date_create()->format('Y-m-d')])
            ->setOrder('sort_order', Collection::SORT_ORDER_DESC);
        if ($this->_request->getActionName() == 'configure') {
            $this->_editOptions = $this->_coreRegistry->registry('current_product')
                ->getPreConfiguredValues()
                ->getOptions();
        }
    }

    /**
     * @param false $asString
     * @return \Magento\Framework\DataObject[]|string
     */
    public function getRentalRule($asString = false)
    {
        return $asString ? $this->json->serialize($this->_rentalRule->getData()) : $this->_rentalRule->getItems();
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRuleString()
    {
        $result = [];
        $count = 0;
        $currency = $this->_storeManager->getStore()->getCurrentCurrency()->getCurrencySymbol();
        foreach ($this->getRentalRule() as $rentalRule) {
            ++$count;
            switch ($rentalRule->getSimpleAction()) {
                case "by_percent":
                    $result[] = __(
                        "%1. %2% off/%3 %4, from %5 to %6",
                        $count,
                        round($rentalRule->getDiscountAmount(), 2),
                        $rentalRule->getRepeatPeriod(),
                        RepeatOptions::MAPPING[$rentalRule->getRepeatType()],
                        $rentalRule->getFromDate(),
                        $rentalRule->getToDate()
                    );
                    break;
                case "by_fixed":
                    $result[] = __(
                        "%1. %2%3 off/%4 %5, from %6 to %7",
                        $count,
                        $currency,
                        round($rentalRule->getDiscountAmount(), 2),
                        $rentalRule->getRepeatPeriod(),
                        RepeatOptions::MAPPING[$rentalRule->getRepeatType()],
                        $rentalRule->getFromDate(),
                        $rentalRule->getToDate()
                    );
                    break;
                case "to_percent":
                    $result[] = __(
                        "%1. %2% off the final price, from %3 to %4",
                        $count,
                        round($rentalRule->getDiscountAmount(), 2),
                        $rentalRule->getFromDate(),
                        $rentalRule->getToDate()
                    );
                    break;
                case "to_fixed":
                    $result[] = __(
                        "%1. %2%3 off the final price, from %4 to %5",
                        $count,
                        $currency,
                        round($rentalRule->getDiscountAmount(), 2),
                        $rentalRule->getFromDate(),
                        $rentalRule->getToDate()
                    );
                    break;
            }

            if ($rentalRule->getStopRulesProcessing()) {
                break;
            }
        }

        return $result;
    }

    /**
     * @return false|string|null
     */
    public function getEditOptions()
    {
        if (!empty($this->_editOptions)) {
            return $this->json->serialize($this->_editOptions);
        }

        return null;
    }

    protected function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrencySymbol()
    {
        return $this->_storeManager->getStore()->getBaseCurrency()->getCurrencySymbol();
    }

    /**
     * @return float|int
     */
    public function getTaxRate()
    {
        if (in_array($this->getConfig(self::XML_PATH_SHOW_TAX_INCLUDE), [2, 3])
            && $this->getConfig(self::XML_PATH_BACKEND_CATALOG_PRICE) != 1) {
            $defaultRate = $this->_taxCalculation->getCalculatedRate(
                $this->_coreRegistry->registry('current_product')->getTaxClassId()
            );

            return 1 + ($defaultRate / 100);
        }

        return 1;
    }

    /**
     * @return array
     */
    public function getRentalPrice()
    {
        $priceData = $this->_rentalPriceFactory->create()->loadByProductId($this->getCurrentProductId())->getData();
        return [
            'id'          => $priceData['id'],
            'product_id'  => $priceData['product_id'],
            'base_price'  => $priceData['base_price'],
            'base_period' => $this->getPeriodStr($priceData['base_period']),
            'base_hour'   => (int)$this->getDuration($priceData['base_period']),
            'base_type'   => substr($priceData['base_period'], -1),
            'add_price'   => $priceData['additional_price'] ?? 0,
            'add_period'  => !empty($priceData['additional_period'])
                ? $this->getPeriodStr($priceData['additional_period']) : null,
            'add_hour'    => !empty($priceData['additional_period'])
                ? (int)$this->getDuration($priceData['additional_period']) : 0,
            'add_type'    => !empty($priceData['additional_period'])
                ? substr($priceData['additional_period'], -1) : null,
        ];
    }

    /**
     * @return array
     */
    public function getRentalOptions()
    {
        return $this->rentalOptionCollection->create()
            ->addFilter('product_id', $this->getCurrentProductId())
            ->getData();
    }

    /**
     * Get encoded days off
     * @return false|string
     */
    public function getDaysOff()
    {
        $daysOff = $this->getConfig(self::XML_PATH_DAYS_OFF);
        $days = strlen($daysOff) > 0 ? explode(',', $daysOff) : [7];

        return $this->json->serialize($days);
    }

    /**
     * Get encoded holidays
     * @return false|string
     */
    public function getHolidays()
    {
        $holidays = $this->getConfig(self::XML_PATH_HOLIDAYS) ?? [0];
        if ($holidays !== [0]) {
            $holidays = array_column($this->json->unserialize($holidays), 'date');
        }

        return $this->json->serialize($holidays);
    }

    /**
     * @return int
     */
    public function isWeekOff()
    {
        $daysOff = $this->getConfig(self::XML_PATH_DAYS_OFF);
        return strpos($daysOff, "1,2,3,4,5,6,0") === 0;
    }

    /**
     * Work hours (range)
     * @return mixed
     */
    public function getWorkHours()
    {
        $workHours = $this->getConfig(self::XML_PATH_WORK_HOURS);
        $hours = strlen($workHours) > 0 ? explode(',', $workHours) : [0, 23];

        return $this->json->serialize($hours);
    }

    /**
     * Get days of week labels
     * @return false|string
     */
    public function getDaysOfWeek()
    {
        $daysOfWeek = $this->getConfig(self::XML_PATH_DAYS_LABEL);
        $labels     = explode(',', $daysOfWeek);

        return $this->json->serialize($labels);
    }

    /**
     * Get month labels
     * @return false|string
     */
    public function getMonthLabels()
    {
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $label    = $this->getConfig(self::XML_PATH_MONTHS_LABEL . $i);
            $months[] = $label;
        }

        return $this->json->serialize($months);
    }

    /**
     * @return mixed
     */
    public function getSelectButton()
    {
        return $this->getConfig(self::XML_PATH_SELECT_TEXT);
    }

    /**
     * @return mixed
     */
    public function getClearButton()
    {
        return $this->getConfig(self::XML_PATH_CLEAR_TEXT);
    }

    /**
     * @return string
     */
    public function getDateFormat()
    {
        $format = $this->getConfig(self::XML_PATH_DATE_FORMAT);

        return strtr($format, ['dd' => 'DD']);
    }

    /**
     * @return string
     */
    public function getTimeFormat()
    {
        $format = $this->getConfig(self::XML_PATH_TIME_FORMAT);

        return strtr($format, ['a' => 'A']);
    }

    /**
     * @return mixed
     */
    public function getFirstDay()
    {
        return $this->getConfig(self::XML_PATH_FIRST_DAY);
    }

    /**
     * @param $optionId
     *
     * @return array
     */
    public function getOptionTypes($optionId)
    {
        return $this->rentalOptionTypeCollection->create()
            ->addFilter('product_id', $this->getCurrentProductId())
            ->addFilter('option_id', $optionId)
            ->getData();
    }

    /**
     * Get maximum advance period duration
     * @return int
     */
    public function getMaxAdvance()
    {
        $advance = (int)$this->getConfig(self::XML_PATH_MAX_ADVANCE);
        return $advance ?? 30;
    }

    /**
     * Get maximum rent duration (days)
     * @return int
     */
    public function getMaxDuration()
    {
        $rental   = $this->_rental;
        $leadTime = $rental->getData('lead_time');
        if ($leadTime == null || $leadTime < 0) {
            $leadTime = 0;
        }

        $duration = $rental->getData('max_duration');
        if ($duration == 0) {
            return (int)$this->getConfig(self::XML_PATH_MAX_DURATION) + (int)$leadTime;
        } else {
            return (int)$duration + (int)$leadTime;
        }
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public function getOptionPriceType($type)
    {
        if ($type == 'per_hour') {
            return 'hour';
        } elseif ($type == 'per_day') {
            return 'day';
        } else {
            return '';
        }
    }

    /**
     * @return array|int|mixed
     */
    public function isShipping()
    {
        $leadTime = $this->_rental->getData('lead_time');
        return in_array($this->_rental->getType(), [0, 2]) && !empty($leadTime) ? $leadTime : 0;
    }

    /**
     * @return bool|mixed
     */
    public function isPickup()
    {
        $address = $this->_rental->getData('pickup_address');
        return in_array($this->_rental->getType(), [1, 2]) && !empty($address) ? $address : false;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return (int)$this->_rental->getData('type');
    }

    /**
     * @return mixed
     */
    public function getGoogleAPIkey()
    {
        return $this->getConfig(self::XML_PATH_GOOGLE_MAP_API_KEY);
    }

    /**
     * @param string $period
     *
     * @return string
     */
    public function getDuration($period)
    {
        return $this->rentalHelper->getDuration($period);
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

    /**]
     * @return false|string
     */
    public function getPriceFormatArray()
    {
        return $this->json->serialize($this->_localeFormat->getPriceFormat());
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
     * @return bool
     */
    public function isPolicyRequired()
    {
        return (!empty($this->getConfig(self::XML_PATH_POLICY_TEXT))
            && $this->getConfig(self::XML_PATH_POLICY_REQUIRED));
    }

    public function getPolicyConfirmation()
    {
        return $this->getConfig(self::XML_PATH_POLICY_CONFIRMATION);
    }

    public function getPolicyErrorMsg()
    {
        return $this->getConfig(self::XML_PATH_POLICY_ERROR);
    }

    /**
     * @return bool
     */
    protected function isFullWidthLayout()
    {
        return $this->pageConfig->getPageLayout() == 'product-full-width';
    }

    /**
     * @return mixed|string|string[]|null
     */
    public function getConfirmationStr()
    {
        $message = $this->getPolicyConfirmation();
        preg_match('#{{(.*?)}}#', $message, $match);
        if (isset($match[1])) {
            if ($this->isFullWidthLayout()) {
                $link = '<a class="action add" id="policy_read" href="#rental_policy-form">' . $match[1] . '</a>';
            } else {
                $link = '<a class="action add" id="policy_read" href="#policy.tab">' . $match[1] . '</a>';
            }
            $message = preg_replace('#{{(.*?)}}#', $link, $message);
        }

        return $message;
    }
}
