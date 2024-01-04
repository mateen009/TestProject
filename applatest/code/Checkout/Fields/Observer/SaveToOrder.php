<?php

namespace Checkout\Fields\Observer;

class SaveToOrder implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    public function __construct(
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }


    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $observer->getEvent()->getOrder();
        $quote = $objectManager->create('Magento\Quote\Api\CartRepositoryInterface')->get($order->getQuoteId());
        if (isset($_COOKIE['clientpo'])) {
            $clientPo = $_COOKIE['clientpo'];
            $order->setData('client_po', $clientPo);
            $quote->setData('client_po', $clientPo);
        }
        if (isset($_COOKIE['costcenter'])) {
            $costCenter = $_COOKIE['costcenter'];
            $order->setData('cost_center', $costCenter);
            $quote->setData('cost_center', $costCenter);

        }
        if (isset($_COOKIE['customershippingaccount'])) {
            $customershippingaccount = $_COOKIE['customershippingaccount'];
            if ($customershippingaccount == 'on') {
                $order->setData('customer_shipping_account', true);
                $quote->setData('customer_shipping_account', true);
            }
        }

        $order->save();
        $quote->save();

        $this->deleteCookie();
        // return $this;
    }

    /** Delete custom Cookie */
    public function deleteCookie()
    {
        if ($this->cookieManager->getCookie('magento2cookie')) {
            $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
            $metadata->setPath('/');

            $this->cookieManager->deleteCookie(
                'clientpo',
                $metadata
            );

            $this->cookieManager->deleteCookie(
                'costcenter',
                $metadata
            );

            $this->cookieManager->deleteCookie(
                'customershippingaccount',
                $metadata
            );
        }
    }
}
