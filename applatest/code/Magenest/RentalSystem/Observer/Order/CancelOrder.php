<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Observer\Order;

use Magenest\RentalSystem\Model\ResourceModel\RentalOrder;
use Magenest\RentalSystem\Model\Status;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magenest\RentalSystem\Model\Rental;
use Magenest\RentalSystem\Model\RentalOrderFactory;

class CancelOrder implements ObserverInterface
{
    /** @var RentalOrderFactory */
    protected $_rentalOrderFactory;

    /** @var RentalOrder */
    private $rentalOrderResource;

    /**
     * CreateRecord constructor.
     *
     * @param RentalOrderFactory $rentalOrderFactory
     * @param RentalOrder $rentalOrderResource
     */
    public function __construct(
        RentalOrderFactory $rentalOrderFactory,
        RentalOrder $rentalOrderResource
    ) {
        $this->_rentalOrderFactory = $rentalOrderFactory;
        $this->rentalOrderResource = $rentalOrderResource;
    }

    /**
     * @param Observer $observer
     *
     * @return $this
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        $orderItem = $observer->getEvent()->getItem();

        if (!$orderItem->getId()) {
            //order not saved in the database
            return $this;
        }

        if ($orderItem->getProductType() != Rental::PRODUCT_TYPE) {
            return $this;
        }

        $product = $orderItem->getProduct();
        if ($product->getTypeId() == Rental::PRODUCT_TYPE) {
            $model = $this->_rentalOrderFactory->create();
            $this->rentalOrderResource->load($model, $orderItem->getId(), 'order_item_id');
            if ($orderItem['qty_invoiced'] == 0) {
                $model->setStatus(Status::CANCELED);
                $this->rentalOrderResource->save($model);
            }
        }

        return $this;
    }
}
