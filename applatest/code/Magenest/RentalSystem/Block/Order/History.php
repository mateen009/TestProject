<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Block\Order;

use Magenest\RentalSystem\Helper\Rental;
use Magento\Framework\Data\Collection;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magenest\RentalSystem\Model\ResourceModel\RentalOrder\CollectionFactory as OrderCollectionFactory;
use Magenest\RentalSystem\Model\RentalFactory;
use Magenest\RentalSystem\Model\ResourceModel\Rental as RentalResource;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Theme\Block\Html\Pager;
use Psr\Log\LoggerInterface;

class History extends Template
{
    /**
     * Google Map API key
     */
    const XML_PATH_GOOGLE_MAP_API_KEY = 'rental_system/general/google_api_key';

    /**
     * Date Time Format
     */
    const XML_PATH_DATE_FORMAT = 'rental_system/locale/dateformat';
    const XML_PATH_TIME_FORMAT = 'rental_system/locale/timeformat';

    /**
     * Rental Calendar
     */
    const XML_PATH_CALENDAR_VALUE   = 'rental_system/rental/calendar';
    const XML_PATH_CALENDAR_PAST    = 'rental_system/rental/calendar_past';
    const XML_PATH_CALENDAR_ONGOING = 'rental_system/rental/calendar_ongoing';
    const XML_PATH_CALENDAR_FUTURE  = 'rental_system/rental/calendar_future';

    /**
     * Template
     * @var string
     */
    protected $_template = 'order/history.phtml';

    /** @var OrderCollectionFactory */
    protected $_orderCollectionFactory;

    /** @var RentalFactory */
    protected $_rentalFactory;

    /** @var RentalResource */
    protected $_rentalResource;

    /** @var \Magento\Customer\Model\Session */
    protected $_customerSession;

    /** @var ProductRepositoryInterface */
    protected $_productInterface;

    /** @var \Magenest\RentalSystem\Model\ResourceModel\RentalOrder\Collection|false */
    protected $rents;

    /** @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface */
    protected $_timezone;

    /** @var PriceHelper */
    protected $priceHelper;

    /** @var Rental */
    private $rentalHelper;

    /** @var LoggerInterface */
    private $logger;

    /** @var Json */
    private $json;

    /**
     * History constructor.
     *
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param RentalFactory $rentalFactory
     * @param RentalResource $rentalResource
     * @param CustomerSession $customerSession
     * @param ProductRepositoryInterface $productInterface
     * @param Context $context
     * @param PriceHelper $priceHelper
     * @param Rental $rentalHelper
     * @param LoggerInterface $logger
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        OrderCollectionFactory $orderCollectionFactory,
        RentalFactory $rentalFactory,
        RentalResource $rentalResource,
        CustomerSession $customerSession,
        ProductRepositoryInterface $productInterface,
        Template\Context $context,
        PriceHelper $priceHelper,
        Rental $rentalHelper,
        LoggerInterface $logger,
        Json $json,
        array $data = []
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_rentalFactory          = $rentalFactory;
        $this->_rentalResource         = $rentalResource;
        $this->_customerSession        = $customerSession;
        $this->_productInterface       = $productInterface;
        $this->priceHelper             = $priceHelper;
        $this->rentalHelper            = $rentalHelper;
        $this->logger                  = $logger;
        $this->json                    = $json;
        $this->_timezone               = $context->getLocaleDate();
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Rental Orders'));
    }

    /**
     * Get Rent Collection
     * @return \Magenest\RentalSystem\Model\ResourceModel\RentalOrder\Collection|bool
     */
    public function getRents()
    {
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }

        if (!$this->rents) {
            $this->rents = $this->_orderCollectionFactory->create()
                ->addFieldToFilter('customer_id', $customerId)
                ->setOrder('main_table.created_at', Collection::SORT_ORDER_DESC)
                ->setOrder('main_table.rental_id', Collection::SORT_ORDER_DESC);
        }

        return $this->rents;
    }

    /**
     * @param $rentalId
     *
     * @return mixed
     */
    public function getProductUrl($rentalId)
    {
        $rentalProduct = $this->_rentalFactory->create();
        $this->_rentalResource->load($rentalProduct, $rentalId);
        try {
            $productUrl = $this->_productInterface->getById($rentalProduct->getProductId())->getProductUrl();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $productUrl = $this->getUrl();
        }

        return $productUrl;
    }

    /**
     * @param $orderId
     *
     * @return string
     */
    public function getOrderUrl($orderId)
    {
        return $this->getUrl('sales/order/view', ['order_id' => $orderId]);
    }

    /**
     * @param $price
     *
     * @return mixed
     */
    public function getFormattedPrice($price)
    {
        return $this->priceHelper->currency($price, true, false);
    }

    /**
     * @param $time
     *
     * @return string
     */
    public function getLocateTime($time)
    {
        return $this->rentalHelper->getDateString($time, '');
    }

    /**
     * @param $time
     *
     * @return string
     */
    public function getTimeCalendar($time)
    {
        return $this->_timezone->formatDateTime($time, 3, 3, null, null, 'yyyy-MM-dd HH:mm:ss');
    }

    /**
     * @return $this|Template
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getRents()) {
            $pager = $this->getLayout()
                ->createBlock(Pager::class, 'rental.order.history.pager')
                ->setAvailableLimit([5 => 5, 10 => 10, 15 => 15, 20 => 20])
                ->setShowPerPage(true)
                ->setCollection($this->getRents());
            $this->setChild('pager', $pager);
            $this->getRents()->load();
        }

        return $this;
    }

    /**
     * Get Google Map Api key
     * @return mixed
     */
    public function getGoogleApiKey()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_GOOGLE_MAP_API_KEY);
    }

    /**
     * @return string
     */
    public function getDisplayCalendar()
    {
        return $this->_scopeConfig->isSetFlag(self::XML_PATH_CALENDAR_VALUE) ? 'block' : 'none';
    }

    /**
     * @return bool|string
     */
    public function getCalendarEventColors()
    {
        $colors   = [];
        $colors[] = '#' . $this->_scopeConfig->getValue(self::XML_PATH_CALENDAR_PAST);
        $colors[] = '#' . $this->_scopeConfig->getValue(self::XML_PATH_CALENDAR_ONGOING);
        $colors[] = '#' . $this->_scopeConfig->getValue(self::XML_PATH_CALENDAR_FUTURE);

        return $this->json->serialize($colors);
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return bool|int|null
     */
    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId() ?? false;
    }
}
