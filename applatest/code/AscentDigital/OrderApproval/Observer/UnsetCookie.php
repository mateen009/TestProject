<?php

namespace AscentDigital\OrderApproval\Observer;

class UnsetCookie implements \Magento\Framework\Event\ObserverInterface

{
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        setcookie("category_tnc_popup", "0", time() - 3600, "/");
    }
}
