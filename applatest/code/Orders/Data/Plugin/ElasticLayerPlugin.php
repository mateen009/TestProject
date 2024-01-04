<?php
namespace Orders\Data\Plugin;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Request\Http;

class ElasticLayerPlugin
{
    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    protected $request;

    protected $_customerSession;
    protected $storeManager;
    protected $categoryFactory;
    protected $_registry;

    /**
     * @param CollectionFactory $productCollectionFactory
     * @param Http $request
     */
    public function __construct(
        CollectionFactory $productCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\Registry $registry,
        Http $request
    ) {
        $this->_customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->categoryFactory = $categoryFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->request = $request;
        $this->_registry = $registry;
    }

    public function beforeQuery($subject, $query)
    {
        $filteredIds = $this->productCollectionFactory->create()
            ->addAttributeToSelect('*');

        $store = $this->storeManager->getStore()->getId();

        $currentCategory = $this->getCurrentCategory();
        $currentCatId = $currentCategory->getId();
        $categoryId = 13; //Fulfill From Seed Stock 
        $subCategoryIds = $this->getAllChildren(true,$categoryId);
        if (in_array($currentCatId, $subCategoryIds)) {
                $customerLocationIds = '';
            if ($this->_customerSession->isLoggedIn()) {
                $customerLocationIds = $this->_customerSession->getCustomer()->getLocationId();
            }

            // && $customerLocationIds != ''
            if($store == 1) { // mobileCg store
                $locationAttribute = 219; // locationId attribute
                $_customerLocationIds = str_replace(",","|",$customerLocationIds);
                $filteredIds->addFieldToFilter('location_id', array(array('regexp' => $_customerLocationIds))); 
                //$filteredIds->addFieldToFilter('qty',['gt'=>0]);  
                //echo $filteredIds->getSelect();//die();
            }
        }

        $filteredIds = $filteredIds->getAllIds();

        //print_r($filteredIds);
        if (!$filteredIds || count($filteredIds) < 1) {
            return [$query];
        }
        
        $query['body']['query']['bool']['filter'] = ['ids' => [ 'values' => $filteredIds]];

        // $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/customQuery.log');
        // $logger = new \Zend_Log();
        // $logger->addWriter($writer);
        // $logger->info('text message');
        // $logger->info(print_r($query, true));
        //echo $query->getSelect();

        return [$query];
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