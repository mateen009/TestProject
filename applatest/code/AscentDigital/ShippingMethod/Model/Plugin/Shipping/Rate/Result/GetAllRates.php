<?php

namespace AscentDigital\ShippingMethod\Model\Plugin\Shipping\Rate\Result;

class GetAllRates
{

    /**
     * Disable the marked shipping rates.
     *
     * NOTE: If you can not see some of the shipping rates, start debugging from here. At first, check 'is_disabled'
     * param in the shipping rate object.
     *
     * @param \Magento\Shipping\Model\Rate\Result $subject
     * @param array $result
     * @return array
     */
    public function afterGetAllRates($subject, $result)
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
        $store = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $storeId = $store->getStore()->getId();
        // Carriers:
        // ups_carrier = 1  
        // usps_carrier = 2  
        // fedex_carrier = 3

        $customerCarrier = $customerSession->getCustomer()->getCustomerCarrier();
        if ($customerCarrier == '1') {
            foreach ($result as $key => $rate) {
                if ($result[$key]->getCarrier() != 'ups_carrier') {
                    unset($result[$key]);
                }
            }
        } elseif ($customerCarrier == '2') {
            foreach ($result as $key => $rate) {
                if ($result[$key]->getCarrier() != 'usps_carrier') {
                    unset($result[$key]);
                }
            }
        } elseif ($customerCarrier == '3') {
            foreach ($result as $key => $rate) {
                if ($result[$key]->getCarrier() != 'fedex_carrier') {
                    unset($result[$key]);
                }
            }
        } else {
            if ($storeId != 7) {


                foreach ($result as $key => $rate) {
                    unset($result[$key]);
                }
            } else {
                foreach ($result as $key => $rate) {
                    if ($result[$key]->getCarrier() != 'flatrate') {
                        unset($result[$key]);
                    }
                }
            }
        }
        return $result;
    }
}
