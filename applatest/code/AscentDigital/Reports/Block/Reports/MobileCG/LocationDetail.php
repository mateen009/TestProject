<?php

namespace AscentDigital\Reports\Block\Reports\MobileCG;

class LocationDetail extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \AscentDigital\NetsuiteConnector\Model\SaveItemDetailsFactory $saveItemDetailsFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager

    )
    {
        $this->productRepository = $productRepository;
        $this->saveItemDetailsFactory = $saveItemDetailsFactory;
        $this->storeManager = $storeManager;
        $this->request = $request;
        parent::__construct($context);
    }
    public function _prepareLayout()
    {
        // $id =$this->request->getParam('ae_id');
        $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();

        if ($breadcrumbsBlock) {

            $breadcrumbsBlock->addCrumb(
                'Location Inventory Report',
                [
                'label' => __('Location Inventory Report'), //lable on breadCrumbes
                'title' => __('Location Inventory Report'),
                'link' => $baseUrl.('customreports/mobilecgreports/locationreport/')
                ]
            );
            $breadcrumbsBlock->addCrumb(
                'Location Detail',
                [
                'label' => __('Location Detail'),
                'title' => __('Location Detail'),
                'link' => '' //set link path
                ]
            );
        }
        // $this->pageConfig->getTitle()->set(__('FAQ')); // set page name
        return parent::_prepareLayout();
    }
    public function getLocationDetails(){
        $locationId = $this->getRequest()->getParam('loc_id');
        
        $locationData = $this->saveItemDetailsFactory->create()->load($locationId);
        return $locationData;
    }
    public function getProduct($productId) {
        try {
        $product = $this->productRepository->getById($productId);
        return $product;

        } catch(\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }
    }
}