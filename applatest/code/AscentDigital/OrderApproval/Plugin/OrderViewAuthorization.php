<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace AscentDigital\OrderApproval\Plugin;

class OrderViewAuthorization implements \Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $orderConfig;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Config $orderConfig
    ) {
        $this->customerSession = $customerSession;
        $this->orderConfig = $orderConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function canView(\Magento\Sales\Model\Order $order)
    {
                $customerId = $this->customerSession->getCustomerId();
                $availableStatuses = $this->orderConfig->getVisibleOnFrontStatuses();
                // if ($order->getId()
                //     && $order->getCustomerId()
                //     && ($order->getCustomerId() == $customerId
                //     || $order->getApproval1Id() == $customerId
                //     || $order->getApproval2Id() == $customerId
                //     || $order->getApproval3Id() == $customerId)
                //     && in_array($order->getStatus(), $availableStatuses, true)
                // ) {
                    return true;
                // }
            
        
        
        return false;
    }
}
