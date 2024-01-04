<?php

namespace AscentDigital\Reports\Controller\Adminhtml\Reports;

use Magento\Framework\Controller\ResultFactory;

class ProductFilter extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\App\Action\Contex
     */
    private $context;

    protected $_storeManager;
    protected $_productCollectionFactory;


    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    ) {
        parent::__construct($context);
        $this->context = $context;
        $this->_storeManager = $storeManager;
        $this->_productCollectionFactory = $productCollectionFactory;
    }

    /**
     * @return json
     */
    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        // sales rep start
        $name = $this->context->getRequest()->getParam('sku');
        if (isset($name) && !empty($name)) {
            $products_dropdown = $this->getProducts($name);
            if ($products_dropdown) {
                $resultJson->setData(["products" => $products_dropdown, "success" => true]);
            } else {
                $resultJson->setData(["success" => false]);
            }
            return $resultJson;
        }
    }

    public function getProducts($name)
    {
        // print_r($email);die;
        $_storeId = $this->_storeManager->getStore()->getId();
        $_storeId = 2; //firstnet

        // $collection = $this->getItemCollection();
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addFieldToFilter('name', array('like' => '%' . $name . '%'))->addStoreFilter($_storeId);
        $selectedSkus = $this->context->getRequest()->getParam('selectedSku');
        if (isset($selectedSkus) && !empty($selectedSkus)) {
            $collection->addFieldToFilter('sku', array('nin' => $selectedSkus));
        }

        // echo "<pre>";print_r($collection->getData());die;

        $productsData = array();

        foreach ($collection as $product) {
            $sku = $product->getSku();
            $pid = $product->getId();
            $pName = $product->getName();
            $productsData[] = '<div id="class-checkbox' . $pid . '"><input type="checkbox" id="checkbox' . $pid . '" value="false" onchange="addSelectOption(\'' . $sku . '\',\'' . $pName . '\',\'#checkbox' . $pid . '\')"><label>' . $pName . '</label></div>';
        }
        if (count($productsData) > 0) {
            $products_dropdown = implode(' ', $productsData);
            return $products_dropdown;
        } else {
            return false;
        }
        // echo "<pre>";print_r($productsData);die();
    }
}
