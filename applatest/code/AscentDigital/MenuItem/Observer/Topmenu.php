<?php

namespace AscentDigital\MenuItem\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Event\ObserverInterface;

class Topmenu implements ObserverInterface
{
    public function __construct()
    {
    }

    public function execute(EventObserver $observer)
    {

        $reportingUrl = "";
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $baseUrl = $storeManager->getStore()->getBaseUrl();
        $storeId = $storeManager->getStore()->getId();

        if ($storeId == 2) {
            $reportingUrl = "reporting";
            $returnUrl = $baseUrl . 'returns';
            $returnTitle = 'Returns';
            $orderingUrl = $baseUrl . 'ordering.html';
        } elseif ($storeId == 1) {
            $orderingUrl = $baseUrl . 'ordering.html';
            $returnUrl = $baseUrl . 'advanceexchange';
            $reportingUrl = $baseUrl . 'reporting';
            $returnTitle = 'Depot Services';
        } else {
            $returnUrl = $baseUrl . 'advanceexchange';
            $reportingUrl = $baseUrl . 'reporting';
            $returnTitle = 'Depot Services';
            $orderingUrl = $baseUrl . 'ordering.html';
        }
        if ($storeId == 5) {
            $menu = $observer->getMenu();
            $tree = $menu->getTree();
            //Ordering top menu code
            $data = [
                'name' => __('Ordering'),
                'id' => 'ordering',
                'url' => $baseUrl,
                'is_active' => false
            ];
            $node = new Node($data, 'id', $tree, $menu);
            $menu->addChild($node);
            //Contact Us top menu code
            $data = [
                'name' => __('Customer Support Information'),
                'id' => 'contact-us',
                'url' => $baseUrl . 'nsconnector/information/information',
                'is_active' => false
            ];
            $node = new Node($data, 'id', $tree, $menu);
            $menu->addChild($node);
        } elseif ($storeId == 7) {
            $menu = $observer->getMenu();
            $tree = $menu->getTree();
            //Ordering top menu code
            $data = [
                'name' => __('Ordering'),
                'id' => 'ordering',
                'url' => $baseUrl,
                'is_active' => false
            ];
            $node = new Node($data, 'id', $tree, $menu);
            $menu->addChild($node);
            //Contact Us top menu code
            // $data = [
            //     'name' => __('Information'),
            //     'id' => 'contact-us',
            //     'url' => $baseUrl . 'nsconnector/information/information',
            //     'is_active' => false
            // ];
            // $node = new Node($data, 'id', $tree, $menu);
            // $menu->addChild($node);
        } else {
            $menu = $observer->getMenu();
            $tree = $menu->getTree();
            //Ordering top menu code
            $data = [
                'name' => __('Ordering'),
                'id' => 'ordering',
                'url' => $orderingUrl,
                'is_active' => false
            ];
            $node = new Node($data, 'id', $tree, $menu);
            $menu->addChild($node);
            //Contact Us top menu code
            $data = [
                'name' => __('Information'),
                'id' => 'contact-us',
                'url' => $baseUrl . 'nsconnector/information/information',
                'is_active' => false
            ];
            $node = new Node($data, 'id', $tree, $menu);
            $menu->addChild($node);
        }
        if ($storeId == 1 || $storeId == 2) {
            //retun top menu code
            $data = [
                'name' => __($returnTitle),
                'id' => 'returns',
                'url' => $returnUrl,
                'is_active' => false
            ];
            $node = new Node($data, 'id', $tree, $menu);
            $menu->addChild($node);
            //reporting top menu code    
            $data = [
                'name' => __('Reporting'),
                'id' => 'reporting',
                'url' => $reportingUrl,
                'is_active' => false
            ];
            $node = new Node($data, 'id', $tree, $menu);
            $menu->addChild($node);
        }
        return $this;
    }
}
