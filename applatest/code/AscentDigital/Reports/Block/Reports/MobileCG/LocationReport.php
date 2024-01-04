<?php

namespace AscentDigital\Reports\Block\Reports\MobileCG;

class LocationReport extends \Magento\Framework\View\Element\Template
{
    protected $salesRep;
    protected $locationCollection;
    protected $_productCollectionFactory;
    protected $csvexportHelper;
    protected $request;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \AscentDigital\NetsuiteConnector\Model\ResourceModel\Collection $locationCollection,
        \AscentDigital\Reports\Model\SalesReps $salesRep,
        \Mobility\QuoteRequest\Model\QuoteRequestFactory $quoteRequestFactory,
        \AscentDigital\Reports\Helper\ExportMobileCgReports $csvexportHelper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Customer\Model\Session $customerSession

    )
    {
        $this->_customerFactory = $customerFactory;
        $this->productRepository = $productRepository;
        $this->salesRep = $salesRep;
        $this->locationCollection = $locationCollection;
        $this->_quoteRequestFactory = $quoteRequestFactory;
        $this->_customerSession = $customerSession;
        $this->csvexportHelper = $csvexportHelper;
        $this->request = $request;
        parent::__construct($context);
    }


    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('My Custom Pagination'));
        parent::_prepareLayout();
        $page_size = $this->getPagerCount();
        $page_data = $this->getLocationDetails();
        if ($this->getLocationDetails()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'custom.pager.name'
            )
                ->setAvailableLimit($page_size)
                ->setShowPerPage(true)
                ->setCollection($page_data);
            $this->setChild('pager', $pager);
            $this->getLocationDetails();
        }
        return $this;
    }
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

   

    public function getPagerCount()
    {
        // get collection
        $minimum_show = 10; // set minimum records
        $page_array = [];
        $list_data = $this->getLocationDetails();
        $list_count = ceil(count($list_data->getData()));
        $show_count = $minimum_show + 1;
        if (count($list_data->getData()) >= $show_count) {
            $list_count = $list_count / $minimum_show;
            $page_nu = $total = $minimum_show;
            $page_array[$minimum_show] = $minimum_show;
            for ($x = 0; $x <= $list_count; $x++) {
                $total = $total + $page_nu;
                $page_array[$total] = $total;
            }
        } else {
            $page_array[$minimum_show] = $minimum_show;
            $minimum_show = $minimum_show + $minimum_show;
            $page_array[$minimum_show] = $minimum_show;
        }
        return $page_array;
    }

    public function getLocationDetails(){
      
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        //show 10 records in one page
        $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 10;
       $customerId = $this->_customerSession->getCustomer()->getId(); 
       $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
       $customerData = $objectManager->create('Magento\Customer\Model\Customer')->load($customerId); 
       
        $locationId = $customerData->getLocationId(); 
        $locationData = $this->locationCollection->addFieldToFilter('location_id', array('in'=> $locationId))
        ->setOrder('id','DESC');

        // echo "<pre>";print_r($locationData->getData());die();

        $dataExport = $this->request->getParam('export_data');
        if(isset($dataExport)) {
            $_locationData = $this->getDataForCsv($locationData);
            $this->csvexportHelper->exportLocationInventoryRportCsv($_locationData);
          }

        //setting page size
        $locationData->setPageSize($pageSize);
        //set current page
            $locationData->setCurPage($page);
        return $locationData;
        
    }

    public function getDataForCsv($locationData)
    {
        $_locationData = array();
        $newLocationData = array();
        if ($locationData && count($locationData)) {
            foreach ($locationData as $_loc) {
                $product = $this->getProduct($_loc->getItemId());
                $_locationData['id'] = $_loc->getId();
                $_locationData['itemId'] = $_loc->getItemId();
                $_locationData['itemName'] = $product->getShortDescription();
                $_locationData['itemSku'] = $product->getName();
                $_locationData['internalId'] = $_loc->getItemInternalId();
                $_locationData['locationId'] = $_loc->getLocationId();
                $_locationData['qty'] = $_loc->getQty();
                $newLocationData[] = $_locationData;
            }
        }

        return $newLocationData;
    }

    public function getProduct($productId) {
        try {
        $product = $this->productRepository->getById($productId);
        return $product;

        } catch(\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }
    }

    // public function getSku($productId){
    //     $sku="";
    //     $collection = $this->_productCollectionFactory->create();
    //     $collection->addAttributeToSelect('*')
    //     ->addFieldToFilter('entity_id', array('in'=> $productId));

    //     foreach($collection as $product) {
    //         $sku = $product->getSku();
    //     }

    //     return $sku;
    // }

    // public function getTitle($productId){
    //     $sku="";
    //     $collection = $this->_productCollectionFactory->create();
    //     $collection->addAttributeToSelect('*')
    //     ->addFieldToFilter('entity_id', array('in'=> $productId));

    //     foreach($collection as $product) {
    //         $sku = $product->getShortDescription();
    //     }

    //     return $sku;
    // }
}