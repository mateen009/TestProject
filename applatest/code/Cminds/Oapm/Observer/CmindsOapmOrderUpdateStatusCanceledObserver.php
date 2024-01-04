<?php
namespace Cminds\Oapm\Observer;

use Cminds\Oapm\Exception\Exception;
use Cminds\Oapm\Exception\InvalidOrderException;

/**
 * Class CmindsOapmOrderUpdateStatusCanceledObserver
 * @package Cminds\Oapm\Observer
 */
class CmindsOapmOrderUpdateStatusCanceledObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $oapmOrder = $observer->getObject();
        $order = $this->orderRepository->get($oapmOrder->getOrderId());

        if (is_null($order->getId())) {
            throw new Exception(__('Order which is bound to provided hash "%1" can not be found.', $oapmOrder->getHash()));
        }

        if ($order->getState() === \Magento\Sales\Model\Order::STATE_CANCELED) {
            return;
        }

        if ($order->canCancel() === false) {
            $message = 'Order number "%1" which is bound to provided hash "%2" should be canceled due to fact that';
            $message .= ' oapm order has been canceled, but operation can not be executed.';

            throw new Exception(__($message, $order->getId(), $oapmOrder->getHash()));
        }

        try {
            $order->cancel();

            $order
                ->addStatusHistoryComment(__('Order has been canceled.'))
                ->setIsVisibleOnFront(true)
                ->setIsCustomerNotified(false);

            $order->save();
        } catch (\Exception $e) {
            throw new Exception(__('During order number "%1" cancel operation something goes wrong.', $order->getId()));
        }

        return;
    }
}
