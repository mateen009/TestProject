<?php
namespace AscentDigital\NetsuiteConnector\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AfterProductDelete implements ObserverInterface
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
        $productId = $product->getId();
        $locationCollection = $this->objectManager->create('\AscentDigital\NetsuiteConnector\Model\ResourceModel\Collection');
        $locations = $locationCollection
        ->addFieldToFilter('item_id', $productId)->load();
        $locationFactory = $this->objectManager->get('\AscentDigital\NetsuiteConnector\Model\SaveItemDetailsFactory');

        foreach ($locations as $location){
            $loc = $locationFactory->create()->load($location->getId());
            $loc->delete();
        }
    }
}