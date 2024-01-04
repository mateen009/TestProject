<?php

namespace AscentDigital\NetsuiteConnector\Cron;

class TestAddProduct
{
    

    public function __construct()
    {
        
    }

    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/crontest.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('TestAddProduct cron is running');
        // die('mateen');

        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // instance of object manager
        // $_product = $objectManager->create('Magento\Catalog\Model\Product');
        // $_product->setName('Test Product');
        // $_product->setTypeId('simple');
        // $_product->setAttributeSetId(4);
        // $_product->setSku('test-SKU');
        // $_product->setWebsiteIds(array(1));
        // $_product->setVisibility(4);
        // $_product->setPrice(array(1));
        // // $_product->setImage('/testimg/test.jpg');
        // // $_product->setSmallImage('/testimg/test.jpg');
        // // $_product->setThumbnail('/testimg/test.jpg');
        // $_product->setStockData(array(
        //         'use_config_manage_stock' => 0, //'Use config settings' checkbox
        //         'manage_stock' => 1, //manage stock
        //         'min_sale_qty' => 1, //Minimum Qty Allowed in Shopping Cart
        //         'max_sale_qty' => 2, //Maximum Qty Allowed in Shopping Cart
        //         'is_in_stock' => 1, //Stock Availability
        //         'qty' => 100 //qty
        //         )
        //     );
        
        $logger->info('TestAddProduct cron is finished');
    }
}