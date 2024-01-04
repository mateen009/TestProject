<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ObserverInterface;
use Magenest\RentalSystem\Helper\Rental as Helper;
use Magento\CatalogInventory\Helper\Data;
use \Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use \Magento\Framework\Message\ManagerInterface;

class QuantityValidator implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var TimezoneInterface
     */
    protected $_timezone;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * Path info update qty in cart
     */
    const PATH_MULTI_SHIPPING_CHECKITEMS   = '/multishipping/checkout/checkItems/';
    const PATH_CHECKOUT_SIDEBAR_UPDATEQTY  = '/checkout/sidebar/updateItemQty/';
    const PATH_CHECKOUT_CART_UPDATEQTY     = '/checkout/cart/updateItemQty/';
    const PATH_CHECKOUT_CART_UPDATEQTY_OLD = '/checkout/cart/updatePost/';
    const PATH_MULTI_SHIPPING_ADDRESS      = '/multishipping/checkout/addressesPost/';

    const PATH_CUSTOMER_SECTION_LOAD = '/customer/section/load/';

    /**
     * QuantityValidator constructor.
     *
     * @param LoggerInterface $logger
     * @param Helper $helper
     * @param RequestInterface $request
     * @param ScopeConfigInterface $scopeConfig
     * @param ManagerInterface $messageManager
     * @param TimezoneInterface $timezone
     * @param Registry $registry
     */
    public function __construct(
        LoggerInterface $logger,
        Helper $helper,
        RequestInterface $request,
        ScopeConfigInterface $scopeConfig,
        ManagerInterface $messageManager,
        TimezoneInterface $timezone,
        Registry $registry
    ) {
        $this->logger         = $logger;
        $this->helper         = $helper;
        $this->_request       = $request;
        $this->_registry      = $registry;
        $this->_scopeConfig   = $scopeConfig;
        $this->messageManager = $messageManager;
        $this->_timezone      = $timezone;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            /* @var $quoteItem \Magento\Quote\Model\Quote\Item */
            $quoteItem = $observer->getEvent()->getItem();
            $id        = $quoteItem->getProduct()->getId();

            if (!$this->helper->isRental($id)) {
                return;
            }

            $pathInfo      = $this->_request->getPathInfo();
            $flag          = 0;
            $flagException = 0;

            // check pathInfo
            if ($pathInfo == self::PATH_CHECKOUT_SIDEBAR_UPDATEQTY
                || $pathInfo == self::PATH_CHECKOUT_CART_UPDATEQTY
                || $pathInfo == self::PATH_MULTI_SHIPPING_ADDRESS
                || $pathInfo == self::PATH_MULTI_SHIPPING_CHECKITEMS
                || $pathInfo == self::PATH_CHECKOUT_CART_UPDATEQTY_OLD
            ) {
                $quoteData = json_decode($quoteItem->getOptions()[0]->getData('value'), true);
            } else if ($pathInfo == self::PATH_CUSTOMER_SECTION_LOAD) {
                return;
            }

            // flag LocalizedException
            if ($pathInfo == self::PATH_CHECKOUT_CART_UPDATEQTY
                || $pathInfo == self::PATH_MULTI_SHIPPING_ADDRESS
                || $pathInfo == self::PATH_MULTI_SHIPPING_CHECKITEMS) {
                $flag = 1;
            }

            $request = $this->_request->getParams();

            if (!empty($request['additional_options']) || !empty($quoteData['additional_options'])) {

                $additionalOptions = !empty($request['additional_options']) ? $request['additional_options'] : $quoteData['additional_options'];

                $priceErr = false;
                $from     = $additionalOptions['rental_from'];
                $to       = $additionalOptions['rental_to'];
                $hours    = $additionalOptions['rental_hours'];

                $validateDuration = $this->helper->validateDuration($id, $hours);
                $validatePrice    = $this->helper->validatePrice($id, $additionalOptions);

                $hour_diff = ceil(($to - $from) / 3600);
                if ((($hour_diff - $hours) != 0) || $validatePrice == false || $validateDuration == false)
                    $priceErr = true;

                if ($priceErr == true)
                    $quoteItem->addErrorInfo(
                        'erro_info',
                        Data::ERROR_QTY,
                        __('An error occurred when adding item to cart. Please refresh the page and try again.')
                    );
                $totalQuoteQty = $this->validateQty($observer);

//                $from = $this->_timezone->formatDateTime(date('Y-m-d H:i', $additionalOptions['rental_from']), 3, 3, null, null, 'yyyy/MM/dd HH:mm');
//                $to   = $this->_timezone->formatDateTime(date('Y-m-d H:i', $additionalOptions['rental_to']), 3, 3, null, null, 'yyyy/MM/dd HH:mm');
                $from = date('Y-m-d H:i:s', $additionalOptions['rental_from']);
                $to   = date('Y-m-d H:i:s', $additionalOptions['rental_to']);
                if (!empty($totalQuoteQty)) {
                    foreach ($totalQuoteQty as $key => $value) {
                        $availableQty = $this->helper->getAvailableQty($key, $from, $to);
                        if ($availableQty < $value) {
                            if ($availableQty <= 0) {
                                $quoteItem->addErrorInfo(
                                    'erro_info',
                                    Data::ERROR_QTY,
                                    __('This product is out of stock.',
                                        $quoteItem->getName(), $availableQty)
                                );
                            } else {
                                $quoteItem->addErrorInfo(
                                    'erro_info',
                                    Data::ERROR_QTY,
                                    __('We don\'t have as many %1 as you requested. Maximum qty you can rent for this duration is %2.',
                                        $quoteItem->getName(), $availableQty)
                                );
                            }
                            if ($flag == 1) {
                                $flagException = 1;
                                throw new LocalizedException(
                                    __('')
                                );
                            }

                        }
                    }
                }
            } else if (isset($request['item_qty']) && $quoteItem->getQty() != $request['item_qty']) {
                $this->_registry->register('update_qty' . $quoteItem->getId(), true);
            } else if ($this->_registry->registry('update_qty' . $quoteItem->getId()) != true && !empty($request) && empty($request['cart'][$quoteItem->getId()])) {
                $quoteItem->addErrorInfo(
                    'erro_info',
                    Data::ERROR_QTY,
                    __('Rent duration not selected.')
                );
            }

        } catch (\Exception $exception) {
            if (isset($quoteItem) && $flagException == 0) {
                $quoteItem->addErrorInfo(
                    'erro_info',
                    Data::ERROR_QTY,
                    __('An error occurred when adding item to cart. Please refresh the page and try again.')
                );
            }
            $this->logger->critical($exception);
        }
    }

    /**
     * @param Observer $observer
     *
     * @return array
     */
    public function validateQty($observer)
    {
        $quoteItems = !empty($observer->getEvent()->getItem()->getquote())
            ? $observer->getEvent()->getItem()->getquote()->getAllItems()
            : $observer->getEvent()->getItem()->getOptions();
        $rentalQty  = [];

        /* @var $quoteItem \Magento\Quote\Model\Quote\Item */
        foreach ($quoteItems as $quoteItem) {
            $productType = $quoteItem->getProductType();
            if ($productType == 'rental') {
                $productId = $quoteItem->getProduct()->getId();
                if (isset($rentalQty[$productId]) && $rentalQty[$productId] > 0)
                    $rentalQty[$productId] += $quoteItem->getQty();
                else $rentalQty[$productId] = $quoteItem->getQty();
            }
        }

        return $rentalQty;
    }
}
