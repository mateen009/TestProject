<?php

namespace AscentDigital\Reports\Block\Reports\MobileCG;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
class MobilityReport extends \AscentDigital\Reports\Block\Reports\MobileCG\Reporting
{

    protected $advanceRep;
     protected $reporting;
   
  
    public function __construct(
        \AscentDigital\Reports\Block\Reports\MobileCG\Reporting $reporting,
        \Magento\Framework\View\Element\Template\Context $context,
        \Custom\AdvanceExchange\Block\Manage\Index $advanceRep,
        \Custom\AdvanceExchange\Block\Manage\AEYTDReport $aEYTDReport,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Sales\Model\Order\ItemFactory $itemCollectionFactory,
        \AscentDigital\Reports\Model\SalesReps $salesRep,
        \Mobility\QuoteRequest\Model\QuoteRequestFactory $quoteRequestFactory,
        \AscentDigital\NetsuiteConnector\Model\ResourceModel\Collection $locationCollection,
        StockRegistryInterface $stockRegistry,
        StockItemRepository $stockItemRepository,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Customer\Model\Session $customerSession
      
    )
    {
        $this->aEYTDReport = $aEYTDReport;
        $this->reporting = $reporting;
        $this->advanceRep = $advanceRep;
        parent::__construct($context,
        $productCollectionFactory,
        $storeManager,
        $orderCollectionFactory,
        $customerFactory,
        $itemCollectionFactory,
        $salesRep,
        $quoteRequestFactory,
        $locationCollection,
        $stockRegistry,
        $stockItemRepository,
        $resourceConnection,
        $customerSession);
    }

    public function getLocationInventoryMobilityReport()
    {
        $location =$this->getLocationInventoryReport()
        ->setOrder('id','DESC');;
        return $location;
    }
    public function getAeYtdMobilityReport()
    {
       $AeYtd = $this->getAeYtdReport();
       return $AeYtd;

    }
    public function getAeYtdMobilityReportByType(){
        $AeYtdType=$this->getAeYtdReportByType();
        return $AeYtdType;
    }
    public function getAdvanceExchangeRecords()
    {
        $records = $this->advanceRep->getAllRecords()
        ->setPageSize(3);
        return $records;
    }
    public function getAeYtdRecords(){
        $record = $this->aEYTDReport->getAllRecords()
        ->setPageSize(3);
        return $record;
    }
}