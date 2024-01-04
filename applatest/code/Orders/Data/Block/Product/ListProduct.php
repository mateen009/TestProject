<?php

namespace Orders\Data\Block\Product;

use Magento\Catalog\Model\Product;

class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{
    protected $_customerSession;
    protected $categoryFactory;
    protected $_registry;

    /**
     * ListProduct constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param Helper $helper
     * @param array $data
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
    \Magento\Catalog\Block\Product\Context $context,
    \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
    \Magento\Catalog\Model\Layer\Resolver $layerResolver,
    \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
    \Magento\Framework\Url\Helper\Data $urlHelper,
    \Magento\Customer\Model\Session $customerSession,
    \Magento\Catalog\Model\CategoryFactory $categoryFactory,
    \Magento\Framework\Registry $registry,
    array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->categoryFactory = $categoryFactory;
        $this->_registry = $registry;
        
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );

    }

    protected function _getProductCollection()
    {
        if ($this->_productCollection === null) {
            $this->_productCollection = $this->initializeProductCollection();
        }

        //echo $this->_productCollection->getSelect();
        return $this->_productCollection;
    }

    /**
     * Get catalog layer model
     *
     * @return Layer
     */
    public function getLayer()
    {
        return $this->_catalogLayer;
    }

    /**
     * Retrieve loaded category collection
     *
     * @return AbstractCollection
     */
    public function getLoadedProductCollection()
    {
        $collection = $this->_getProductCollection();

        $categoryId = $this->getLayer()->getCurrentCategory()->getId();
        foreach ($collection as $product) {
            $product->setData('category_id', $categoryId);
        }

        return $collection;
    }

    private function initializeProductCollection()
    {
        $layer = $this->getLayer();
        /* @var $layer Layer */
        if ($this->getShowRootCategory()) {
            $this->setCategoryId($this->_storeManager->getStore()->getRootCategoryId());
        }

        // if this is a product view page
        if ($this->_coreRegistry->registry('product')) {
            // get collection of categories this product is associated with
            $categories = $this->_coreRegistry->registry('product')
                ->getCategoryCollection()->setPage(1, 1)
                ->load();
            // if the product is associated with any category
            if ($categories->count()) {
                // show products from this category
                $this->setCategoryId($categories->getIterator()->current()->getId());
            }
        }

        $origCategory = null;
        if ($this->getCategoryId()) {
            try {
                $category = $this->categoryRepository->get($this->getCategoryId());
            } catch (NoSuchEntityException $e) {
                $category = null;
            }

            if ($category) {
                $origCategory = $layer->getCurrentCategory();
                $layer->setCurrentCategory($category);
            }
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $category = $objectManager->get('Magento\Framework\Registry')->registry('current_category'); //get current category
        $ids = [$category->getId()];
        $proFactory = $objectManager->get('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        $productCollection = $proFactory->create();
        $store = $this->_storeManager->getStore()->getStoreId();
        // $productCollection->addAttributeToSelect('*')->addCategoriesFilter(['in' => $ids])->addStoreFilter($store);

        $collection = $layer->getProductCollection();

        $currentCategory = $this->getCurrentCategory();
        $currentCatId = $currentCategory->getId();
        $categoryId = 13; //Fulfill From Seed Stock 
        $subCategoryIds = $this->getAllChildren(true,$categoryId);
        if (in_array($currentCatId, $subCategoryIds)) {
            $customerLocationIds = 0;
            if ($this->_customerSession->isLoggedIn()) {
                $customerLocationIds = $this->_customerSession->getCustomer()->getLocationId();
            }

            //&& $customerLocationIds != ''
            if($store == 1 && $customerLocationIds) { // mobileCg store 
                //echo $customerLocationIds.":here";
            // $testCollection = $productCollection;
            //18,23,45,32
                $locationAttribute = 219; // locationId attribute
               // if($_customerLocationIds != 0) {
                $_customerLocationIds = str_replace(",","|",$customerLocationIds);
                if($customerLocationIds == 0) {
                    $_customerLocationIds = 0;
                }
                //echo $_customerLocationIds.":here";
                $collection->getSelect()->joinLeft(array('entity_varchar' => 'catalog_product_entity_varchar'),
                'e.entity_id = entity_varchar.entity_id')
                                                    ->where(
                                                        'entity_varchar.attribute_id=?',
                                                        $locationAttribute
                                                    ) 
                                                    ->where(
                                                        'entity_varchar.value regexp ?',
                                                        $_customerLocationIds
                                                    )
                                                    ->group('e.entity_id');                                    
                                                  // $collection->addFieldToFilter('stock_status_index.qty', ['gt' => 1])  ;                                 
                // $collection->getSelect()->joinLeft(array('stock_item' => 'cataloginventory_stock_item'),
                // 'e.entity_id = stock_item.product_id')
                //                                     ->where(
                //                                         'stock_item.qty>?',
                //                                         0
                //                                     );    
                                                                                                          
                // echo $collection->getSelect();die();
            }
        }
        
        // // $productCollection->addCategoriesFilter(['in' => $ids]);
        // // $proCollection = $productCollection->addStoreFilter($store->getId());
        // // foreach ($productCollection as $p) {
        // //     print_r($p->getName());
        // // }
        // // die;
        
        // $collection = $productCollection;

        $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());

        if ($origCategory) {
            $layer->setCurrentCategory($origCategory);
        }

        $this->_eventManager->dispatch(
            'catalog_block_product_list_collection',
            ['collection' => $collection]
        );
        //echo $collection->getSelect();
        return $collection;
    }

    public function getCurrentCategory()
    {        
        return $this->_registry->registry('current_category');
    }

    public function getCategory($categoryId)
    {
        $category = $this->categoryFactory->create()->load($categoryId);
        return $category;
    }

    public function getAllChildren($categoryId, $asArray = false)
    {
        return $this->getCategory($categoryId)->getAllChildren($asArray);
    }


}