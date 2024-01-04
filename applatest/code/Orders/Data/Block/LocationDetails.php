<?php
namespace Orders\Data\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;

class LocationDetails extends Template
{
    /**
     * @var Registry
     */
    protected $registry;
    protected $request;
    protected $_productCollectionFactory;
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * ProductView constructor.
     * @param Template\Context $context
     * @param array $data
     * @param Registry $registry
     */
    public function __construct(
        Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\App\Request\Http $request,
        Registry $registry,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->request = $request;
        $this->_productCollectionFactory = $productCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {


        $sku = $this->request->getParam('skus');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $query="";
        if(isset($sku)){
            $query = "SELECT nametable.value, catalog_product_entity.sku ,item.id,item.item_id,item.item_internal_id,item.location_id,item.qty  FROM items_details as item
            LEFT JOIN `catalog_product_entity_varchar`   AS nametable ON item.item_id  = nametable.entity_id
            LEFT JOIN catalog_product_entity 
                   ON nametable.entity_id = catalog_product_entity.entity_id 
           WHERE  nametable.attribute_id = (SELECT attribute_id 
                                      FROM   `eav_attribute` 
                                      WHERE  `entity_type_id` = 4 
                                             AND `attribute_code` LIKE 'name') AND  catalog_product_entity.sku='".$sku."'";
        }else{
            $query = "SELECT nametable.value, catalog_product_entity.sku ,item.id,item.item_id,item.item_internal_id,item.location_id,item.qty  FROM items_details as item
        LEFT JOIN `catalog_product_entity_varchar`   AS nametable ON item.item_id  = nametable.entity_id
        LEFT JOIN catalog_product_entity 
               ON nametable.entity_id = catalog_product_entity.entity_id 
       WHERE  nametable.attribute_id = (SELECT attribute_id 
                                  FROM   `eav_attribute` 
                                  WHERE  `entity_type_id` = 4 
                                         AND `attribute_code` LIKE 'name') ";
        }
        
        $results = $connection->fetchAll($query);
        return $results;


          

    }
}