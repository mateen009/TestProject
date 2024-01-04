<?php

namespace AscentDigital\NetsuiteConnector\Block\Adminhtml\Product;

use Magento\Customer\Block\Account\SortLinkInterface;
use Magento\Framework\View\Element\Html\Link\Current;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\CatalogInventory\Api\StockRegistryInterface;

class LocationDetails extends \Magento\Backend\Block\Template
{
    protected $_customerSession;
    protected $_productCollectionFactory;
    private $getSalableQtyDataBySku;
    protected $stockItemRepository;
    protected $stockRegistry;
    protected $request;
    protected $locationCollection;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \AscentDigital\NetsuiteConnector\Model\ResourceModel\Collection $locationCollection,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        GetSalableQuantityDataBySku $getSalableQtyDataBySku,
        StockItemRepository $stockItemRepository,
        StockRegistryInterface $stockRegistry,
        array $data = []
     ) {
         $this->_customerSession = $customerSession;
         $this->storeManager = $storeManager;
         $this->request = $request;
         $this->locationCollection = $locationCollection;
         $this->_productCollectionFactory = $productCollectionFactory;
         $this->getSalableQtyDataBySku = $getSalableQtyDataBySku;
         $this->stockItemRepository = $stockItemRepository;
         $this->stockRegistry = $stockRegistry;
         parent::__construct($context, $data);
     }
     
     public function getProduct(){

        $sku = $this->request->getParam('skus');
        $email = $this->request->getParam('customeremail');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $query="SELECT attribute_id FROM   `eav_attribute` WHERE  `entity_type_id` = 4 AND `attribute_code` LIKE 'name'";
        $attributeId = $connection->fetchCol($query);

        $collection = $this->locationCollection;
        if(isset($email) && $email!=''){
            $this->getProductsByCustomerEmail($attributeId,$email);
        }
        
        if(isset($sku) && $sku!=''){
            $this->getProductsBySkus($attributeId,$sku,$collection);
        }else{
            $collection->getSelect()->joinLeft(
                ['cpev' => 'catalog_product_entity_varchar'],
                'main_table.item_id = cpev.entity_id'
            )->joinLeft(
                ['cpe' => 'catalog_product_entity'],
                'cpev.entity_id = cpe.entity_id'
            )->where(
                'cpev.attribute_id=?',
                $attributeId
            ); 
        }
        
        $results = $collection->getData();
        return $results;

    }

    public function getProductsByCustomerEmail($attributeId,$email){

       $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
       $CustomerModel = $objectManager->create('Magento\Customer\Model\Customer');
       $CustomerModel->setWebsiteId(1);
       $customerData = $CustomerModel->loadByEmail($email);

       $locationId = $customerData->getLocationId();
       $locationCollection = $this->locationCollection;
       $collection = $locationCollection->addFieldToFilter('location_id',['in' => $locationId]);
        
            $collection->getSelect()->joinLeft(
                ['cpevc' => 'catalog_product_entity_varchar'],
                'main_table.item_id = cpevc.entity_id'
            )->joinLeft(
                ['cpet' => 'catalog_product_entity'],
                'cpevc.entity_id = cpet.entity_id'
            )->where(
                'cpevc.attribute_id=?',
                $attributeId
            ); 
            $results = $collection->getData();
            return $results;

    }

    public function getProductsBySkus($attributeId,$sku,$locationCollection){

        $locationCollection->getSelect()->joinLeft(
            ['cpev' => 'catalog_product_entity_varchar'],
            'main_table.item_id = cpev.entity_id'
        )->joinLeft(
            ['cpe' => 'catalog_product_entity'],
            'cpev.entity_id = cpe.entity_id'
        )->where(
            'cpev.attribute_id=?',
            $attributeId
        )->where(
            'cpe.sku=?',
            $sku
        );

        $results = $locationCollection->getData();
        return $results;
    }
}