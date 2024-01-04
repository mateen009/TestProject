<?php

namespace AscentDigital\Reports\Controller\SalesManager;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
    
class SendOrderDueBackReportEmail extends Action
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
            'subject'      => 'Due Back Order Report'
            ];
           
           $customerObj = $this->objectManager()->create('Magento\Customer\Model\Customer')->getCollection();

           foreach($customerObj as $customerObjdata) {
              $customer = $this->objectManager()->create('Magento\Customer\Model\Customer')
              ->load($customerObjdata->getData('entity_id'));
            if($customer->getData('send_customers_email')=='1'){
             if($customer->getData('Customer_Type')==3){
                 if(!empty($this->generatePdf($customer->getData('entity_id'),$customer->getData('Customer_Type')))){
                    $filePath = 'https://caad034362.nxcli.net/pub/media/';
                    $fileName = "OrderDetailReport.pdf";
                    $fromEmail = "waqas011fa@gmail.com";
                    $fromName = 'Ascent Digital';
                    $fromdata = ['email' => $fromEmail, 'name' => $fromName];
                    $to =  'waqasawan012fa@gmail.com';
                    $emailtempalte = 11;
                    $transport = $transportBuilder->setTemplateIdentifier($emailtempalte)
                    ->setTemplateOptions($templateOptions)->setTemplateVars($templateVars)
                    ->addAttachment(file_get_contents($this->generatePdf($customer->getData('entity_id'),$customer->getData('Customer_Type'))),'Due Back Order Report' ,'application/pdf')->setFrom($fromdata)->addTo($to)->getTransport();
                   $transport->sendMessage();
                                           echo "message sent successfully with attachment";
                   $pdf = $mediapath.'/ascent_pdf/'.$customer->getData('entity_id').' DueBackReport.pdf';
                     //After sending due back order pdf to sales manager delete it from directory media/ascent_pdf
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
        
        
          //        generater sales manager wise due back orders pdf and save it in media/ascent_pdf then send this pdf to sales manager.
        
            
            $orderCollectionFactory = $this->objectManager()->get('\Magento\Sales\Model\ResourceModel\Order\CollectionFactory');
        
            $fileSystem = $this->objectManager()->create('\Magento\Framework\Filesystem');
            $mediaPath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();

            
            $baseurl = $this->storeManager()->getStore()->getBaseUrl();
            $mediaurl = $this->storeManager()->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            
            $orderTo = date('Y-m-d H:i:s');
            $orders = $orderCollectionFactory->create($this->getManagerReps($customerId,$customerType))->addFieldToSelect(
                '*'
            )->addAttributeToFilter('due_date', array('from' => '2020-10-12 09:20:25', 'to' => $orderTo))->addFieldToFilter(
                'return_status',
                ['nin' => 'yes']
            )->addAttributeToFilter('status',['in' => 'shipping', 'complete', 'processing'])
                ->setOrder(
                    'created_at',
                    'desc'
            );
        if(!empty($orders->getData())){
            $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('Ascent Digital');
                $pdf->SetTitle('Due Back Order Report');
                $pdf->SetSubject('Due Back Order Report');
                $pdf->SetKeywords('Due Back Order Report');

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
            
            
                $html='<h1>Due Back Orders Report</h1>';
                $html .= '<table style="font-family: Arial, Helvetica, sans-serif;border-collapse: collapse;width: 100%;"> <tr>
                <th  style="width:15%;height:30px;border: 1px solid #ddd;padding: 8px;padding-top: 12px;padding-bottom: 12px;text-align: left;background-color: black;color:white;">Quote #</th>
                 
                 <th style="width:30%;border: 1px solid #ddd;height:30px;padding: 8px;padding-top: 12px;padding-bottom: 12px;text-align: left;background-color: black;color:white;">Email</th>
                 
                <th style="width:15%;border: 1px solid #ddd;height:30px;padding: 8px;padding-top: 12px;padding-bottom: 12px;text-align: left;background-color: black;color:white;">Order Total</th>
            
                <th style="width:20%;border: 1px solid #ddd;height:30px;padding: 8px;padding-top: 12px;padding-bottom: 12px;text-align: left;background-color: black;color:white;">Due Date</th>
                <th style="width:15%;border: 1px solid #ddd;height:30px;padding: 8px;padding-top: 12px;padding-bottom: 12px;text-align: left;background-color: black;color:white;">Order Status</th>
                <th style="width:10%;border: 1px solid #ddd;height:30px;padding: 8px;padding-top: 12px;padding-bottom: 12px;text-align: left;background-color: black;color:white;">SO #</th>
               </tr>';
               foreach ($orders as $order) {
               $html .= '
                      <tr>
                        
                        <td style="width:15%;height:30px;border: 1px solid #ddd;padding: 8px;">'.$order->getIncrementId().'</td>
                        <td style="width:30%;border: 1px solid #ddd;height:30px;padding: 8px;">'.$order->getCustomerEmail().'</td>
                        <td style="width:15%;height:30px;border: 1px solid #ddd;padding: 8px;">'.'$'.number_format($order->getGrandTotal(),2).'</td>
                        <td style="width:20%;border: 1px solid #ddd;height:30px;padding: 8px;">'.$order->getCustomerTs().'</td>
                        <td style="width:15%;border: 1px solid #ddd;height:30px;padding: 8px;">'.$order->getStatus().'</td>
                        <td style="width:10%;border: 1px solid #ddd;height:30px;padding: 8px;">'.$order['ns_so_number'].'</td>
                      </tr>';
               }
               $html .= '</table>';
               $pdf->writeHTML($html, true, false, true, false, '');
               
             $pdf->lastPage();

             $directory = $this->objectManager()->get('\Magento\Framework\Filesystem\DirectoryList');
             $mediapath = $directory->getPath('media');
             $pdf->Output($mediapath.'/ascent_pdf/'.$customerId.' DueBackReport.pdf', 'F');
             return 'https://caad034362.nxcli.net/pub/media/ascent_pdf/'.$customerId.' DueBackReport.pdf';
        }
            
                
    }

}
