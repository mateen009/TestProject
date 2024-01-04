<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Observer\Order;

use Magenest\RentalSystem\Model\Status;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magenest\RentalSystem\Model\Rental;
use Magenest\RentalSystem\Model\RentalFactory;
use Magenest\RentalSystem\Model\RentalOrderFactory;
use Magenest\RentalSystem\Helper\Rental as Helper;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Model\OrderFactory;
use Magenest\RentalSystem\Model\ResourceModel\RentalOrder;
use Psr\Log\LoggerInterface;

class CreateOrderRental implements ObserverInterface
{
    const XML_PATH_CODE_PATTERN = 'rental_system/general/pattern_code';

    /** @var Helper */
    protected $_helper;

    /** @var RentalFactory */
    protected $_rentalFactory;

    /** @var RentalOrderFactory */
    protected $_rentalOrderFactory;

    /** @var SearchCriteriaBuilder */
    protected $_searchCriteria;

    /** @var OrderItemRepositoryInterface */
    protected $_orderItemInterface;

    /** @var OrderFactory */
    protected $_order;

    /** @var RentalOrder */
    protected $_resourceOrder;

    /** @var LoggerInterface */
    private $logger;

    /**
     * CreateOrderRental constructor.
     *
     * @param Helper $helper
     * @param RentalFactory $rentalFactory
     * @param SearchCriteriaBuilder $searchCriteria
     * @param RentalOrderFactory $rentalOrderFactory
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param OrderFactory $order
     * @param LoggerInterface $logger
     * @param RentalOrder $resourceRental
     */
    public function __construct(
        Helper $helper,
        RentalFactory $rentalFactory,
        SearchCriteriaBuilder $searchCriteria,
        RentalOrderFactory $rentalOrderFactory,
        OrderItemRepositoryInterface $orderItemRepository,
        OrderFactory $order,
        LoggerInterface $logger,
        RentalOrder $resourceRental
    ) {
        $this->_helper             = $helper;
        $this->_rentalFactory      = $rentalFactory;
        $this->_rentalOrderFactory = $rentalOrderFactory;
        $this->_searchCriteria     = $searchCriteria;
        $this->_orderItemInterface = $orderItemRepository;
        $this->_order              = $order;
        $this->logger              = $logger;
        $this->_resourceOrder      = $resourceRental;
    }

    /**
     * @param Observer $observer
     *
     * @return $this
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        try {
            $orderIds       = $observer->getEvent()->getOrderIds();
            $searchCriteria = $this->_searchCriteria->addFilter('order_id', $orderIds, 'in')->create();
            $orderItems     = $this->_orderItemInterface->getList($searchCriteria)->getItems();
            foreach ($orderItems as $orderItem) {
                if ($orderItem->getProductType() == Rental::PRODUCT_TYPE) {
                    $orderId = $orderItem->getOrderId();
                    $order   = $this->_order->create()->loadByAttribute('entity_id', $orderId);
                    if ($orderItem->getQtyOrdered() > 0) {
                        /** @var \Magenest\RentalSystem\Model\RentalOrder $model */
                        $model      = $this->_rentalOrderFactory->create()->loadByOrderItemId($orderItem->getItemId());
                        $buyRequest = $orderItem->getProductOptions();
                        $options    = $buyRequest['info_buyRequest']['additional_options'];
                        $fromStamp  = $options['rental_from'];
                        $toStamp    = $options['rental_to'];
                        $from       = date('Y-m-d H:i', $fromStamp);
                        $to         = date('Y-m-d H:i', $toStamp);

                        /** @var \Magenest\RentalSystem\Model\Rental $rentalModel */
                        $rentalModel = $this->_rentalFactory->create()->loadByProductId($orderItem->getProductId());
                        $delivery    = $options['local_pickup'] ?? 0;
                        if ($delivery) {
                            $delivery_value = __("Address") . ": " . $rentalModel->getData('pickup_address');
                        } else {
                            $delivery_value = __("Lead time") . ": " . $rentalModel->getData('lead_time');
                        }
                        $address = $order->getShippingAddress() ?? $order->getBillingAddress();
                        $status  = !empty($model->getStatus()) ? $model->getStatus() : Status::UNPAID;
                        $code    = !empty($model->getCode()) ? $model->getCode() : $this->_helper->generateCode();
                        $information = " ";
                        if (array_key_exists('options', $options)) {
                            $information = $this->_helper->decodeOptions($options['options']);
                        }
                        $data = [
                            'order_item_id'      => $orderItem->getItemId(),
                            'order_increment_id' => $order->getIncrementId(),
                            'order_id'           => $orderId,
                            'price'              => $orderItem->getBasePrice(),
                            'start_time'         => $from,
                            'end_time'           => $to,
                            'code'               => $code,
                            'qty'                => $orderItem->getQtyOrdered(),
                            'qty_invoiced'       => 0,
                            'status'             => $status,
                            'title'              => $orderItem->getName(),
                            'rental_id'          => $rentalModel->getId(),
                            'information'        => $information,
                            'type'               => $rentalModel->getData('type'),
                            'customer_id'        => $order->getCustomerId(),
                            'customer_name'      => $order->getCustomerName(),
                            'customer_email'     => $order->getCustomerEmail(),
                            'customer_address'   => $this->_helper->getAddressStr($address),
                            'delivery_value'     => $delivery_value,
                        ];

                        $rentalOrderModel = $model->addData($data);
                        $this->_resourceOrder->save($rentalOrderModel);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }

        return $this;
    }
}
