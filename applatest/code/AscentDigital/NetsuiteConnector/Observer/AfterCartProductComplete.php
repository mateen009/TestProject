<?php
namespace AscentDigital\NetsuiteConnector\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AfterCartProductComplete implements ObserverInterface
{
    /*
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {
        $this->objectManager = $objectmanager;
    }
    public function execute(Observer $observer)
    {
        $product = $observer->getProduct();
        $resuest = $observer->getRequest();
        $response = $observer->getResponse();

        // echo "<pre>";
        // print_r($product->getId());
        // print_r($resuest);
        // // print_r($response->getData());
        // die;

        // $productId = $product->getId();
    }
}