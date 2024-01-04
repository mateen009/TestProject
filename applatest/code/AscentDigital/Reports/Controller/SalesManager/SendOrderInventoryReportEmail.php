<?php

namespace AscentDigital\Reports\Controller\SalesManager;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
    
class SendOrderInventoryReportEmail extends Action
{

    public function __construct(
         
        Context $context
    ) {
        
        parent::__construct($context);
    }

       public function execute()
       {
           $directory = $this->objectManager()->get('\Magento\Framework\Filesystem\DirectoryList');
           $mediapath = $directory->getPath('media');
           
           $transportBuilder = $this->objectManager()->create('\AscentDigital\Reports\Model\Mail\Template\TransportBuilder');
           
           $templateOptions = [
                       'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                       'store' => 1
                     ];
            $templateVars = [
            'subject'      => 'Order Inventory Report'
            ];
           
           $customerObj = $this->objectManager()->create('Magento\Customer\Model\Customer')->getCollection();

           foreach($customerObj as $customerObjdata) {
              $customer = $this->objectManager()->create('Magento\Customer\Model\Customer')
              ->load($customerObjdata->getData('entity_id'));
            if($customer->getData('send_customers_email')=='1'){
             if($customer->getData('Customer_Type')==3){
                 if(!empty($this->generatePdf($customer->getData('entity_id'),$customer->getData('Customer_Type')))){
                    $filePath = 'https://caad034362.nxcli.net/pub/media/';
                    $fileName = "OrderInventoryReport.pdf";
                    $fromEmail = "waqas011fa@gmail.com";
                    $fromName = 'Ascent Digital';
                    $fromdata = ['email' => $fromEmail, 'name' => $fromName];
                    $to =  'waqasawan012fa@gmail.com';
                    $emailtempalte = 11;
                    $transport = $transportBuilder->setTemplateIdentifier($emailtempalte)
                    ->setTemplateOptions($templateOptions)->setTemplateVars($templateVars)
                    ->addAttachment(file_get_contents($this->generatePdf($customer->getData('entity_id'),$customer->getData('Customer_Type'))),'Order Inventory Report' ,'application/pdf')->setFrom($fromdata)->addTo($to)->getTransport();
                   $transport->sendMessage();
                                           echo "message sent successfully with attachment";
                   $pdf = $mediapath.'/ascent_pdf/'.$customer->getData('entity_id').' OrderInventoryReport.pdf';
                     //After sending inventory order pdf to sales manager delete it from directory media/ascent_pdf
                   if(file_exists($pdf)) {
                   unlink($pdf);
                   }
                }
               }
            }
           }
       }
    
    public function objectManager(){
       $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager;
    }
    
    
    public function storeManager(){
        
        $storeManager = $this->objectManager()->get(\Magento\Store\Model\StoreManagerInterface::class);
        return $storeManager;
    }
    
    public function isFirstNet()
    {
        $currentWebsiteId = $this->storeManager()->getStore()->getWebsiteId();
        if ($currentWebsiteId == '3') {
            return true;
        }
        return false;
    }
    
    
    public function getManagerReps($customerId,$customerType)
    {
        
        $customerFactory = $this->objectManager()->get('\Magento\Customer\Model\CustomerFactory');
         $firstnet = $this->isFirstNet();
               if(!$firstnet){
                   return $customerId;
               }
               //get all reps data
               $customers = array();
               if ($customerType == 3) {
                   $customers[]=$customerId;
                   //get sales manager reps
                   $customerData = $customerFactory->create()->getCollection()->addAttributeToSelect("entity_id")
                       ->addAttributeToFilter("SalesManager_ID", $customerId)->load();
                   foreach ($customerData->getData() as $data) {
                       $customers[] = $data['entity_id'];
                   }
                   return $customers;
               }else if ($customerType == 4) {
                   $customers[]=$customerId;
                   //get sales manager reps
                   $customerData = $customerFactory->create()->getCollection()->addAttributeToSelect("entity_id")
                       ->addAttributeToFilter("TerritoryManager_ID", $customerId)->load();
                   foreach ($customerData->getData() as $data) {
                       $customers[] = $data['entity_id'];
                   }
                   return $customers;
               }else if ($customerType == 5) {
                   $customers[]=$customerId;
                   //get sales manager reps
                   $customerData = $customerFactory->create()->getCollection()->addAttributeToSelect("entity_id")
                       ->addAttributeToFilter("Executive_ID", $customerId)->load();
                   foreach ($customerData->getData() as $data) {
                       $customers[] = $data['entity_id'];
                   }
                   return $customers;
               }
       
        
    }
    
    public function generatePdf($customerId,$customerType){
        
        
          //        generater sales manager wise  orders inventory pdf and save it in media/ascent_pdf then send this pdf to sales manager.
        
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
            $baseUrl = $storeManager->getStore()->getBaseUrl();
        
            $orderCollectionFactory = $this->objectManager()->get('\Magento\Sales\Model\ResourceModel\Order\CollectionFactory');
        
            $fileSystem = $this->objectManager()->create('\Magento\Framework\Filesystem');
            $mediaPath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();

            
            $baseurl = $this->storeManager()->getStore()->getBaseUrl();
            $mediaurl = $this->storeManager()->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        
        
        $orders = $orderCollectionFactory->create($this->getManagerReps($customerId,$customerType))->addAttributeToSelect('*')->addFieldToFilter(
            'status',
            array("in",'Shipping','Processing','Complete')
        );
        $orders->getSelect()->join(array('order_item' => 'sales_order_item'),'main_table.entity_id = order_item.order_id');
        $orders->addExpressionFieldToSelect('grand_total', 'SUM({{grand_total}})', 'grand_total')->addExpressionFieldToSelect('total_qty_ordered', 'SUM({{total_qty_ordered}})', 'total_qty_ordered');
        $orders->addExpressionFieldToSelect('grand_total', 'SUM({{grand_total}})', 'grand_total');
        
        $orders->getSelect()->group('order_item.sku');
        
        
        if(!empty($orders->getData())){
            $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('Ascent Digital');
                $pdf->SetTitle('Order Inventory Report');
                $pdf->SetSubject('Order Inventory Report');
                $pdf->SetKeywords('Order Inventory Report');

                $PDF_HEADER_LOGO_WIDTH = "30";
                $PDF_HEADER_TITLE = "";
                $PDF_HEADER_STRING = "";

                $pdf->SetHeaderData(PDF_HEADER_LOGO, $PDF_HEADER_LOGO_WIDTH, $PDF_HEADER_TITLE, $PDF_HEADER_STRING);

                //$pdf->SetHeaderData(logopath, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

                // set header and footer fonts
                $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
                $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

                $pdf->SetFont('dejavusans', '', 10);

                $pdf->AddPage();
            
                $html='<h1>Order Inventory Report</h1>';
            $html .= '<table style="font-family: Arial, Helvetica, sans-serif;border-collapse: collapse;width: 100%;"> <tr>
                           <th  style="width:40%;height:30px;border: 1px solid #ddd;padding: 8px;padding-top: 12px;padding-bottom: 12px;text-align: left;background-color: black;color:white;">Item Name</th>
                            <th style="width:20%;border: 1px solid #ddd;height:30px;padding: 8px;padding-top: 12px;padding-bottom: 12px;text-align: left;background-color: black;color:white;">Total Orders</th>
                            <th style="width:15%;border: 1px solid #ddd;height:30px;padding: 8px;padding-top: 12px;padding-bottom: 12px;text-align: left;background-color: black;color:white;">Grand Total</th>
                           <th style="width:20%;border: 1px solid #ddd;height:30px;padding: 8px;padding-top: 12px;padding-bottom: 12px;text-align: left;background-color: black;color:white;">Total Qty Ordered</th>
                          </tr>';
                          foreach ($orders as $order) {
                              foreach($this->getOrderProduct($order->getSku()) as $item){
                          $html .= '
                                 <tr>
                                   
                                   <td style="width:40%;height:30px;border: 1px solid #ddd;padding: 8px;">'.$item->getName().'</td>
                                   <td style="width:20%;border: 1px solid #ddd;height:30px;padding: 8px;">'.$this->getOrderCount($order->getSku(),$customerId,$customerType).'</td>
                                   <td style="width:15%;border: 1px solid #ddd;height:30px;padding: 8px;">'.$order->formatPrice($order->getGrandTotal()).'</td>
                                   <td style="width:20%;height:30px;border: 1px solid #ddd;padding: 8px;">'.number_format($order->getTotalQtyOrdered()) .'</td>
                                   
                                   
                                 </tr>';
                              }
                          }
                          $html .= '</table>';
                
                
                $pdf->writeHTML($html, true, false, true, false, '');
               
             $pdf->lastPage();

             $directory = $this->objectManager()->get('\Magento\Framework\Filesystem\DirectoryList');
             $mediapath = $directory->getPath('media');
             $pdf->Output($mediapath.'/ascent_pdf/'.$customerId.' OrderInventoryReport.pdf', 'F');
            return $baseUrl.'pub/media/ascent_pdf/'.$customerId.' OrderInventoryReport.pdf';
        }
            
                
    }
    
    public function getOrderCount($sku,$customerId,$customerType){
        
         $orderCollectionFactory = $this->objectManager()->get('\Magento\Sales\Model\ResourceModel\Order\CollectionFactory');
        
        
        $orders = $orderCollectionFactory->create($this->getManagerReps($customerId,$customerType))->addAttributeToSelect('*')->addFieldToFilter(
            'status',
            array("in",'Shipping','Processing','Complete')
        );
       $orders->getSelect()->join(array('order_item' => 'sales_order_item'),'main_table.entity_id = order_item.order_id');
       $orders->addFieldToFilter('order_item.sku', array(array('like' => '%'.$sku.'%')));
        $orders->addExpressionFieldToSelect('grand_total', 'SUM({{grand_total}})', 'grand_total')->addExpressionFieldToSelect('total_qty_ordered', 'SUM({{total_qty_ordered}})', 'total_qty_ordered');
         $orders->getSelect()->group('main_table.entity_id');
        $count = count($orders);
        return $count;
    }
    
   public function getOrderProduct($sku){
       
       $productCollectionFactory = $this->objectManager()->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
       $collection = $productCollectionFactory->create();
       $collection->addFieldToSelect('*');
       $collection->addAttributeToFilter('sku', ['in' => array($sku)]);
       return $collection;
   }
}


