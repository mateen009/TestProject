<?php

namespace AscentDigital\NetsuiteConnector\Plugin;

class ShippingStatus
{
    protected $_order;

    public function __construct(
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Sales\Model\OrderRepository $order
    ) {
        $this->_order = $order;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    public function afterExecute(\Magento\Shipping\Controller\Adminhtml\Order\Shipment\Save $subject)
    {
        $orderId = $subject->getRequest()->getParam('order_id');
        $order = $this->_order->get($orderId);
        $order->setStatus('shipping');
        $order->save();
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
        return $resultRedirect;
    }
}
