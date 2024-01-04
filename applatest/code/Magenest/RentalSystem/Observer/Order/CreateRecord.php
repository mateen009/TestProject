<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Observer\Order;

use Magenest\RentalSystem\Model\Status;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\ProductFactory;
use Magenest\RentalSystem\Model\Rental;
use Magenest\RentalSystem\Model\RentalFactory;
use Magenest\RentalSystem\Model\ResourceModel\Rental as RentalResource;
use Magenest\RentalSystem\Model\RentalOrderFactory;
use Magenest\RentalSystem\Helper\Rental as Helper;
use Magento\Sales\Model\Order;
use Magenest\RentalSystem\Model\ResourceModel\RentalOrder;
use Magento\Sales\Model\Order\Item;
use Psr\Log\LoggerInterface;

/**
 * Class CreateRecord
 * Update status Order Rental after Invoice Order.
 */
class CreateRecord implements ObserverInterface
{
    const XML_PATH_CODE_PATTERN = 'rental_system/general/pattern_code';

    /** @var Helper */
    protected $_helper;

    /** @var RentalFactory */
    protected $_rentalFactory;

    /** @var RentalResource */
    protected $_rentalResource;

    /** @var RentalOrderFactory */
    protected $_rentalOrderFactory;

    /** @var RentalOrder */
    protected $_resourceOrder;

    /** @var LoggerInterface */
    protected $_logger;

    /**
     * CreateRecord constructor.
     *
     * @param Helper $helper
     * @param RentalFactory $rentalFactory
     * @param RentalResource $rentalResource
     * @param Order $order
     * @param RentalOrderFactory $rentalOrderFactory
     * @param RentalOrder $resourceRental
     * @param LoggerInterface $_logger
     */
    public function __construct(
        Helper $helper,
        RentalFactory $rentalFactory,
        RentalResource $rentalResource,
        Order $order,
        RentalOrderFactory $rentalOrderFactory,
        RentalOrder $resourceRental,
        LoggerInterface $_logger
    ) {
        $this->_helper             = $helper;
        $this->_rentalFactory      = $rentalFactory;
        $this->_rentalResource     = $rentalResource;
        $this->_rentalOrderFactory = $rentalOrderFactory;
        $this->_resourceOrder      = $resourceRental;
        $this->_logger             = $_logger;
    }

    /**
     * @param Observer $observer
     *
     * @return $this
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        /** @var Item $orderItem */
        $orderItem = $observer->getEvent()->getItem();

        if (!$orderItem->getId()) {
            //order not saved in the database
            return $this;
        }

        if ($orderItem->getProductType() != Rental::PRODUCT_TYPE) {
            return $this;
        }

        $product = $orderItem->getProduct();
        try {
            if ($product->getTypeId() == Rental::PRODUCT_TYPE) {
                $order = $orderItem->getOrder();
                $model = $this->_rentalOrderFactory->create()->loadByOrderItemId($orderItem->getId());
                //in case of converting guest with order to customer, also update customer_id of rental item
                if ($order->getCustomerId() && $model->getId() && !$model->getData('customer_id')) {
                    $model->setData('customer_id', $order->getCustomerId());
                    $this->_resourceOrder->save($model);
                } elseif ($order->hasInvoices() && $orderItem->getQtyInvoiced() > 0) {

                    if ($model->getStatus() == Status::UNPAID) {
                        $model->setData('status', Status::PENDING);
                    }

                    $model->setData('qty_invoiced', $orderItem->getQtyInvoiced());
                    if ($model->getId()) {
                        $productId   = $orderItem->getProductOptions()['info_buyRequest']['product'];
                        $rentalModel = $this->_rentalFactory->create();
                        $this->_rentalResource->load($rentalModel, $productId, 'product_id');
                        $invoicedQtyChanged = $orderItem->getQtyInvoiced() - $orderItem->getOrigData('qty_invoiced');
                        //only update qty rented and send receipt if the invoiced qty has changed
                        if ($invoicedQtyChanged != 0) {
                            $qtyRented       = $rentalModel->getData('qty_rented');
                            $updateQtyRented = $qtyRented + $invoicedQtyChanged;
                            $rentalModel->setData('qty_rented', $updateQtyRented);
                            $this->_rentalResource->save($rentalModel);
                            $this->_helper->sendReceipt($model->getData());
                        }
                    }

                    $this->_resourceOrder->save($model);
                }
            }
        } catch (\Exception $e) {
            $this->_logger->debug($e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }

        return $this;
    }
}
