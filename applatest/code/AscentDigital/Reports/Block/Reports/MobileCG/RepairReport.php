<?php

namespace AscentDigital\Reports\Block\Reports\MobileCg;

use Magento\Customer\Block\Account\SortLinkInterface;
use Magento\Framework\View\Element\Html\Link\Current;

class RepairReport extends Current
{
    protected $_customerSession;
    protected $_orderCollectionFactory;
    protected $resourceConnection;
    protected $csvexportHelper;
    protected $request;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\App\Request\Http $request,
        \AscentDigital\Reports\Helper\ExportMobileCgReports $csvexportHelper,
        array $data = []
     ) {
         $this->_customerSession = $customerSession;
         $this->storeManager = $storeManager;
         $this->_orderCollectionFactory = $orderCollectionFactory;
         $this->resourceConnection = $resourceConnection;
         $this->csvexportHelper = $csvexportHelper;
         $this->request = $request;
         parent::__construct($context, $defaultPath, $data);
     }

     public function getExchangeItemsData() {
        $data = $this->getRecordsFromDb();
        return $data;
     }

     private function getRecordsFromDb() {
         $query = 'SELECT ri.*, r.title as reason, ic.title as conditionTitle, soi.name as productName, s.title as statusTitle, re.created_at, 
                    re.customer_name FROM `amasty_rma_request_item` ri 
                   LEFT JOIN `amasty_rma_reason` r ON ri.reason_id = r.reason_id
                   LEFT JOIN `amasty_rma_item_condition` ic ON ri.condition_id = ic.condition_id
                   LEFT JOIN `sales_order_item` soi ON ri.order_item_id = soi.item_id
                   LEFT JOIN `amasty_rma_status` s ON ri.item_status = s.status_id
                   LEFT JOIN `amasty_rma_request` re ON ri.request_id = re.request_id
                   WHERE ri.reason_id = 10';
          $data = $this->resourceConnection->getConnection()->fetchAll($query);
        //   foreach($data as $result) {
        //       echo "<pre>";print_r($result);die();
        //   }
        $dataExport = $this->request->getParam('export_data');
        if(isset($dataExport)) {
            $this->csvexportHelper->exportRepairRportCsv($data);
          }

         return $data;
     }
 
}