<?php

namespace Ibnab\CustomLinked\Block;

use Magento\Framework\View\Element\Html\Link\Current;

class CustomLinked extends Current
{
    protected $_customerSession;
    protected $resourceConnection;
    protected $registry;
    protected $_productloader; 

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        array $data = []
     ) {
         $this->_customerSession = $customerSession;
         $this->resourceConnection = $resourceConnection;
         $this->registry = $registry;
         $this->_productloader = $_productloader;
         parent::__construct($context, $defaultPath, $data);
     }

     public function getLoadProduct($id)
    {
        return $this->_productloader->create()->load($id);
    }

     public function getCustomLinkedProducts() {
        $product_id = $this->getCurrentProduct();
        $data = $this->getRecordsFromDb($product_id);
        return $data;
     }

     public function getCurrentProduct()
    {
      $currentProduct = $this->registry->registry('current_product');
        return $currentProduct->getId();
    }

     private function getRecordsFromDb($product_id) {
         $query = 'SELECT * FROM `catalog_product_link` WHERE product_id = '.$product_id.' AND link_type_id = 17';
         $data = $this->resourceConnection->getConnection()->fetchAll($query);
        //   foreach($data as $result) {
        //       echo "<pre>";print_r($result);die();
        //   }
         return $data;
     }
 
}