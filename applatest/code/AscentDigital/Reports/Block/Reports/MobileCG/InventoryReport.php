<?php

namespace AscentDigital\Reports\Block\Reports\MobileCg;

use Magento\Customer\Block\Account\SortLinkInterface;
use Magento\Framework\View\Element\Html\Link\Current;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\CatalogInventory\Api\StockRegistryInterface;

class InventoryReport extends Current
{
    protected $_customerSession;
    protected $_productCollectionFactory;
    private $getSalableQtyDataBySku;
    protected $stockItemRepository;
    protected $stockRegistry;
    protected $csvexportHelper;
    protected $request;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        GetSalableQuantityDataBySku $getSalableQtyDataBySku,
        StockItemRepository $stockItemRepository,
        StockRegistryInterface $stockRegistry,
        \Magento\Framework\App\Request\Http $request,
        \AscentDigital\Reports\Helper\ExportMobileCgReports $csvexportHelper,
        array $data = []
     ) {
         $this->_customerSession = $customerSession;
         $this->storeManager = $storeManager;
         $this->_productCollectionFactory = $productCollectionFactory;
         $this->getSalableQtyDataBySku = $getSalableQtyDataBySku;
         $this->stockItemRepository = $stockItemRepository;
         $this->stockRegistry = $stockRegistry;
         $this->request = $request;
         $this->csvexportHelper = $csvexportHelper;
         parent::__construct($context, $defaultPath, $data);
     }
     
     public function getProductsData()
     {
        $_storeId = $this->storeManager->getStore()->getId();
        $dataExport = $this->request->getParam('export_data');

        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addStoreFilter($_storeId);
        $collection->setPageSize(20);
        //echo count($collection);die(':here');
        $productsData = array();

        foreach($collection as $product) {
          $sku = $product->getSku();
          $pid = $product->getId();
          $pName = $product->getName();
          //$salableQty = $this->getSalableQtyBySku($sku);
         // $salableQty = $this->getStockItem($pid);
         $_salableQty = $this->getStockItemData($pid);
         // $salableQty = $product->getId();
          $productsData[$pid]['sku'] = $sku;
          $productsData[$pid]['name'] = $pName;
          $productsData[$pid]['qty'] = $_salableQty;
          //echo $product->getSku()." : ".$salableQty."<br/>";
        }

        if(isset($dataExport)) {
         $this->csvexportHelper->exportInventoryRportCsv($productsData);
       }
        //echo "<pre>";print_r($productsData);die();
        return $productsData;
     }

     public function getStockItemData($productId)
     {
        $productStock = $this->stockRegistry->getStockItem($productId);
        return $productStock->getQty();
     }

     public function getStockItem($productId)
     {
        $productStock = $this->stockItemRepository->get($productId);
        return $productStock->getQty();
     }

     public function getSalableQtyBySku($sku)
       {
            $salable = $this->getSalableQtyDataBySku->execute($sku);
            // if(isset($salable[0]['qty'])) {
            //   return $salable[0]['qty'];
            // } else {
            //   return 0;
            // }
            //echo "<pre>";print_r($salable[0]);die();
            return json_encode($salable);
       }
}