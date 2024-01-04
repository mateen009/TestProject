<?php

namespace AscentDigital\NetsuiteConnector\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use AscentDigital\NetsuiteConnector\Helper\ProductHelper;
use Magento\Framework\Filesystem\DirectoryList as Directory;

class Save extends Action
{
    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $directory;

    /**
     * @var \AscentDigital\NetsuiteConnector\Helper\ProductHelper
     */
    protected $productHelper;

    protected $resultFactory;
    protected $storeManager;
    protected $messageManager;

    public function __construct(
        Context $context,
        Directory $directory,
        ProductHelper $productHelper,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->directory = $directory;
        $this->productHelper = $productHelper;
        $this->resultFactory = $resultFactory;
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
    }


    public function execute()
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/netsuite_manual_cron.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info("Update Product by sku.");
        // root directory path
        $root = $this->directory->getRoot();
        require_once($root . '/lib/PHPToolkit/NetSuiteService.php');
        $params = $this->getRequest()->getPost();
        $skus = $params['product_sku'];
        if (isset($skus) && !empty($skus)) {
            $skuArray = explode(",", $skus);     // explode skus by coma 
            // get product by sku from netsuite and add it in the magento if the product already exist in the magento then it will update that product
            foreach ($skuArray as $sku) {
                $this->productHelper->getProductBySku($sku, $root, $logger);
            }
        } else {
            // updated products in last 24hrs
            $this->productHelper->getUpdatedProducts($root, $logger);
            $this->messageManager->addSuccess(__('Product updated successfully!'));
        }
        // $redirect->setUrl($baseUrl . 'nsproduct/product/add/');
        $redirectUrl = $this->getUrl('nsproduct/product/add');
        $redirect->setUrl($redirectUrl);
        return $redirect;
    }
}
