<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Observer\Product;

use Magenest\RentalSystem\Model\Status;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Action\Context;
use Magenest\RentalSystem\Model\RentalFactory;
use Magenest\RentalSystem\Model\ResourceModel\RentalOrder\CollectionFactory;
use Magento\Framework\Exception\StateException;

class SaveBeforeObserver implements ObserverInterface
{
    /** @var Context */
    protected $context;

    /** @var \Magento\Framework\App\RequestInterface */
    protected $_request;

    /** @var RentalFactory */
    protected $_rentalFactory;

    /** @var CollectionFactory */
    protected $_rentalOrderCollection;

    /**
     * SaveBeforeObserver constructor.
     *
     * @param RentalFactory $rentalFactory
     * @param CollectionFactory $rentalOrderFactory
     * @param Context $context
     */
    public function __construct(
        RentalFactory $rentalFactory,
        CollectionFactory $rentalOrderFactory,
        Context $context
    ) {
        $this->_rentalFactory         = $rentalFactory;
        $this->_rentalOrderCollection = $rentalOrderFactory;
        $this->context                = $context;
        $this->_request               = $context->getRequest();
    }

    /**
     * @param Observer $observer
     *
     * @throws StateException
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product       = $observer->getEvent()->getProduct();
        $productTypeId = $product->getTypeId();
        if ($productTypeId == "rental") {
            //if total qty is subtracted, check if this qty is lower than the rented qty of any ongoing rents
            if (isset($product->getStoredData()['quantity_and_stock_status']['qty'])
                && isset($product->getQuantityAndStockStatus()['qty'])) {
                $newQty = $product->getQuantityAndStockStatus()['qty'];
                $oldQty = $product->getStoredData()['quantity_and_stock_status']['qty'];
                if ($newQty < $oldQty) {
                    $this->checkQtyChange($newQty, $product->getId(), $product->getName());
                }
            }
            $stockData                            = $product->getStockData();
            $stockData['manage_stock']            = 0;
            $stockData['use_config_manage_stock'] = 0;
            $product->setStockData($stockData);
            //set product price equal to base rental price
            $params = $this->_request->getParams();
            if (isset($params['rental'])) {
                $rentalData = $params['rental'];
                if (!empty($rentalData['row'][0]['base_price'])) {
                    $price = $rentalData['row'][0]['base_price'];
                    $product->setPrice($price);
                    $product->setBasePrice($price);
                }
            }

            if (!isset($product->getQuantityAndStockStatus()['qty']) && $product->isObjectNew()) {
                $this->context->getMessageManager()->addWarningMessage(__('Rental Qty is not set'));
            }
        }
    }

    /**
     * @param $newQty
     * @param $productId
     * @param $name
     *
     * @throws StateException
     */
    public function checkQtyChange($newQty, $productId, $name)
    {
        $rentalId = $this->_rentalFactory->create()->loadByProductId($productId)->getId();
        //filter rents by product ID, status not canceled, ends not before current date
        $rents = $this->_rentalOrderCollection->create()
            ->addFieldToFilter('rental_id', $rentalId)
            ->addFieldToFilter('status', ['neq' => Status::CANCELED])
            ->addFieldToFilter('end_time', ['gteq' => date('Y-m-d H:i:s')]);
        if ($rents->count()) {
            $format    = 'Y-m-d';
            $step      = '+1 day';
            $rentedQty = [];
            /** @var \Magenest\RentalSystem\Model\RentalOrder $rent */
            foreach ($rents as $rent) {
                $qty     = $rent->getData('qty');
                $current = strtotime($rent->getData('start_time'));
                $end     = strtotime($rent->getData('end_time'));
                //Iterate through each day in the rent duration to accumulate rented qty
                while ($current < $end) {
                    $date = date($format, $current);
                    if (!isset($rentedQty[$date])) {
                        $rentedQty[$date] = $qty;
                    } else {
                        $rentedQty[$date] += $qty;
                    }
                    if ($rentedQty[$date] > $newQty) {
                        throw new StateException(__(
                            'Cannot save rental product %1: the new qty (%2) is lower than total rented qty on %3 (%4)',
                            $name,
                            $newQty,
                            $date,
                            $rentedQty[$date]
                        ));
                    }
                    $current = strtotime($step, $current);
                }
            }
        }
    }
}
