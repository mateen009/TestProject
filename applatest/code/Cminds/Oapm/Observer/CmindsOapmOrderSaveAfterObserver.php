<?php
namespace Cminds\Oapm\Observer;

/**
 * Class CmindsOapmOrderSaveAfterObserver
 * @package Cminds\Oapm\Observer
 */
class CmindsOapmOrderSaveAfterObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager = null;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->eventManager = $eventManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $oapmOrder = $observer->getObject();
        if ($oapmOrder->dataHasChangedFor('status')
            && (int) $oapmOrder->getStatus() === \Cminds\Oapm\Model\Order::STATUS_CANCELED
        ) {
            $this->eventManager->dispatch(
                \Cminds\Oapm\Model\Order::EVENT_STATUS_UPDATE_CANCELED,
                $oapmOrder->getEventData()
            );
        }

        return;
    }
}
