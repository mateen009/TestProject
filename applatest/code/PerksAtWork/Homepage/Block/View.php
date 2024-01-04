<?php

namespace PerksAtWork\Homepage\Block;

class View extends \Magento\Framework\View\Element\Template
{
    

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    public function getProducts()
    {
      
        $productCollection = $this->collectionFactory->create();
        $productCollection->addAttributeToSelect('*')
        ->addWebsiteFilter(5)
        // ->addFieldToFilter('homepage','yes')
        ->addFieldToFilter('type_id','configurable');
        
        return $productCollection;
    
         }

}
